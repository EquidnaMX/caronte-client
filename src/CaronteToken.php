<?php

namespace Gruelas\Caronte;

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
    public const COOKIE_NAME = 'caronte_token';

    /**
     * Create a new class instance.
     */
    private function __construct()
    {
        //
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
        $config = static::getConfig(signing_key: config('caronte.APP_SECRET'));
        $token  = static::decodeToken(config: $config, raw_token: $raw_token);

        try {
            $config->validator()->assert(
                $token,
                ...static::getConstraints(config: $config)
            );
        } catch (RequiredConstraintsViolated $e) {
            throw new Exception($e->getMessage(), 401);
        }

        try {
            $config->validator()->assert(
                $token,
                new StrictValidAt(SystemClock::fromUTC())
            );

            $user    = json_decode($token->claims['user']);
            $headers = [
                'caronte_token' => $token->toString()
            ];
        } catch (RequiredConstraintsViolated $e) {
            $new_token_str = static::exchangeToken(raw_token: $raw_token);
            $new_token     = static::decodeToken(config: $config, raw_token: $new_token_str);
            $user          = json_decode($new_token->claims['user']);
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
            $caronte_response = Http::withHeaders(
                [
                    'Authorization' => 'Bearer ' . $raw_token,
                ]
            )->get(config('caronte.URL') . 'api/tokens/exchange');

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
     * Retrieves the configuration for generating a JWT token
     *
     * @param string $signing_key The signing key used for generating the token.
     * @return Configuration The configuration object for generating the token.
     */
    public static function getConfig(string $signing_key): Configuration
    {
        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($signing_key)
        );

        return $config;
    }

    /**
     * Decodes a JWT token.
     *
     * @param Configuration $config The configuration object.
     * @param string $raw_token The raw token to decode.
     * @return JWTToken The decoded JWT token.
     * @throws Exception If the token is not provided, is malformed, or is invalid.
     */
    public static function decodeToken(Configuration $config, string $raw_token): JWTToken
    {
        if (empty($raw_token)) {
            throw new Exception('Token not provided', 400);
        }

        if (count(explode(".", $raw_token)) != 3) {
            throw new Exception('Malformed token', 400);
        }

        $token = $config->parser()->parse($raw_token);

        if (!isset($token->claims['user'])) {
            throw new Exception('Invalid token', 401);
        }

        return $token;
    }

    /**
     * Retrieves the constraints for validating a JWT token.
     *
     * @param Configuration $config The configuration object.
     * @return array The array of validation constraints.
     */
    public static function getConstraints(Configuration $config): array
    {
        $constraints = [];

        if (config('caronte.ENFORCE_ISSUER')) {
            $constraints[] = new IssuedBy(config('caronte.ISSUER_ID'));
        }

        $constraints[] = new SignedWith($config->signer(), $config->signingKey());

        return $config->validationConstraints(
            $constraints
        );
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
}
