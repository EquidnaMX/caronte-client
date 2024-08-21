<?php

namespace Gruelas\Caronte;

use App\Classes\Application\Application as ApplicationClass;
use App\Classes\Token\Token;

use App\Models\Application;
use App\Models\User;

use App\Http\Middleware\Auth\ValidationResponse;

use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cookie;
use Lcobucci\JWT\Signer\Key\InMemory;
use Illuminate\Support\Facades\Http;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token as JWTToken;
use Lcobucci\JWT\Configuration;
use Lcobucci\Clock\SystemClock;
use Exception;

class CaronteToken
{
    /**
     * Create a new class instance.
     */
    private function __construct(private string $signing_key)
    {
        //
    }

    public const COOKIE_NAME = 'caronte_token';


    /**
     * Create a new instance with a specified signing key for JWT validation.
     *
     * @param string $signing_key The signing key to be used for JWT validation.
     * @return static A new instance of the class with the signing key set.
     */
    public static function setSigningKey(string $signing_key): static
    {
        return new static($signing_key);
    }

    /**
     * Validate the provided raw token.
     *
     * @param string|null $raw_token The raw token string to validate.
     * @return ValidationResponse if the token is valid, error otherwise.
     * @throws ValidationException If the token validation fails.
     */
    public static function validateToken(string $raw_token): ValidationResponse
    {
        $token     = static::decodeToken(raw_token: $raw_token);

        try {
            $application = Application::where('cn', $token->claims()->get('sub'))->firstOrFail();
            $application = ApplicationClass::fromModel($application);
        } catch (Exception $e) {
            throw new Exception('Subject not found', 404);
        }

        $caronte_token = static::setSigningKey($application->getSigningKey());

        $config    = $caronte_token->getConfig();
        $validator = $config->validator();

        try {
            if (config('caronte.ENFORCE_ISSUER')) {
                $validator->assert($token, new IssuedBy(config('caronte.ISSUER_ID')));
            }

            $validator->assert($token, new SignedWith(...$caronte_token->getSignerData()));
        } catch (RequiredConstraintsViolated $e) {
            throw new Exception($e->getMessage(), 401);
        }

        try {
            $validator->assert($token, new StrictValidAt(SystemClock::fromUTC()));

            $user    = json_decode($token->claims()->get('user'));
            $headers = ['caronte_token' => $token->toString()]; # TODO add token ¿?
        } catch (RequiredConstraintsViolated $e) {
            $new_token_str = static::exchangeToken(raw_token: $raw_token);
            $new_token     = static::decodeToken(raw_token: $new_token_str);

            $user = json_decode($new_token->claims()->get('user'));
            $headers = [
                'caronte_token' => $new_token_str
            ];
        }

        return new ValidationResponse($user, $headers);
    }

    /**
     * Exchange the provided raw token for a new token from Caronte API.
     *
     * @param string $raw_token The raw token string to exchange.
     * @return string The new token string received from the Caronte API.
     * @throws Exception If the token exchange fails.
     */
    public static function exchangeToken(string $raw_token): string
    {
        try {
            $caronte_response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $raw_token,
            ])->get(config('caronte.URL') . 'api/tokens/exchange');

            if ($caronte_response->failed()) {
                throw new RequestException($caronte_response);
            }

            $new_token_str = $caronte_response->body();

            if (!CaronteHelper::isAPI()) {
                self::setFileToken(token_id: Cookie::get(static::COOKIE_NAME), token_str: $new_token_str);
            }

            return $new_token_str;
        } catch (RequestException $e) {
            if (!CaronteHelper::isAPI()) {
                static::forgetCookie();
            }

            throw new Exception('Cannot exchange token', 400);
        }
    }

    /**
     * Decodes the provided raw token.
     *
     * @param string $raw_token The raw token string to decode.
     * @return JWTToken The decoded Token instance.
     * @throws Exception If the token is invalid or not provided.
     */
    public static function decodeToken(string $raw_token): JWTToken
    {
        if (empty($raw_token)) {
            throw new Exception('Token not provided', 400);
        }

        if (count(explode(".", $raw_token)) != 3) {
            throw new Exception('Malformed token', 400);
        }

        $token = Token::parseToken(token_str: $raw_token);

        if (!$token->claims()->has('user')) {
            throw new Exception('Invalid token', 401);
        }

        return $token;
    }

    /**
     * Remove the Caronte token from the cookie and local storage.
     *
     * @return void
     */
    public static function forgetCookie(): void
    {
        if (Storage::disk('local')->exists('tokens/' . Cookie::get(static::COOKIE_NAME))) {
            Storage::disk('local')->delete('tokens/' . Cookie::get(static::COOKIE_NAME));
        }

        Cookie::queue(Cookie::forget(static::COOKIE_NAME));
    }

    /**
     * Update local user data.
     *
     * @param stdClass $user The user object containing updated data.
     * @return void
     */
    public static function updateUserData($user): void
    {
        try {
            $local_user = User::findOrFail($user->uri_user);
        } catch (Exception $e) {
            $local_user = User::create([
                'uri_user' => $user->uri_user,
                'email'    => $user->email,
            ]);
        }

        $local_user->update(['name' => $user->name]);
        $local_user->metadata()->delete();

        foreach ($user->metadata as $metadata) {
            $local_user->metadata()->create([
                'uri_user'  => $user->uri_user,
                'value'     => $metadata->value,
                'key'       => $metadata->key, # TODO add scope
            ]);
        }
    }

    /**
     * Set the Caronte token in a cookie.
     *
     * @param string $token_id The token ID to store in the cookie.
     * @return void
     */
    public static function setCookie(string $token_id): void
    {
        Cookie::queue(Cookie::forever(static::COOKIE_NAME, $token_id));
    }

    /**
     * Store the Caronte token in a local file.
     *
     * @param null|string $token_id The token ID to use as the filename.
     * @param string $token_str The token string to store.
     * @return void
     */
    public static function setFileToken(null|string $token_id, string $token_str): void
    {
        Storage::disk('local')->put('tokens/' . $token_id, $token_str);
    }

    /**
     * Get the JWT configuration for token validation.
     *
     * @return Configuration The JWT configuration instance.
     */
    public function getConfig(): Configuration
    {
        $config = Configuration::forSymmetricSigner(...$this->getSignerData());
        return $config;
    }

    /**
     * Get the signer data used for JWT token validation.
     *
     * @return array An array containing the signer and signing key.
     */
    public function getSignerData(): array
    {
        return [
            new Sha256(),
            $this->getSigningKey()
        ];
    }

    /**
     * Get the signing key used for JWT token validation.
     *
     * @return InMemory The signing key for JWT validation.
     */
    public function getSigningKey(): InMemory
    {
        return InMemory::plainText($this->signing_key);
    }
}
