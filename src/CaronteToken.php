<?php

namespace Equidna\Caronte;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\Clock\SystemClock;
use Caronte;
use Exception;

class CaronteToken
{
    private function __construct()
    {
        //
    }

    public static function validateToken(string $raw_token): Plain
    {
        $config = static::getConfig();
        $token  = static::decodeToken(raw_token: $raw_token);

        try {
            /*$config->validator()->assert(
                $token,
                ...static::getConstraints()
            );*/
        } catch (RequiredConstraintsViolated $e) {
            throw new Exception($e->getMessage());
        }

        try {
            $config->validator()->assert(
                $token,
                new StrictValidAt(SystemClock::fromUTC())
            );

            return $token;
        } catch (RequiredConstraintsViolated $e) {
            return static::exchangeToken(raw_token: $raw_token);
        }
    }

    public static function exchangeToken(string $raw_token): Plain
    {
        try {
            $caronte_response = Http::withOptions(
                [
                    'verify' => !config('caronte.ALLOW_HTTP_REQUESTS')
                ]
            )->withHeaders(
                [
                    'Authorization' => 'Bearer ' . $raw_token,
                ]
            )->get(config('caronte.URL') . 'api/tokens/exchange');

            if ($caronte_response->failed()) {
                throw new RequestException($caronte_response);
            }

            $token = static::validateToken($caronte_response->body());

            Caronte::setTokenWasExchanged();

            return $token;
        } catch (RequestException $e) {
            Caronte::clearToken();
            throw new Exception('Cannot exchange token');
        }
    }

    /**
     * Decodes a token using the provided configuration and raw token string.
     *
     * @param Configuration $config The configuration object used for decoding the token.
     * @param string $raw_token The raw token string to be decoded.
     * @return Plain The decoded token.
     * @throws Exception If the token is not provided, is malformed, is invalid, or does not contain a 'user' claim.
     */
    public static function decodeToken(string $raw_token): Plain
    {
        if (empty($raw_token)) {
            throw new Exception('Token not provided', 400);
        }

        if (count(explode(".", $raw_token)) != 3) {
            throw new Exception('Malformed token', 400);
        }

        $token = static::getConfig()->parser()->parse($raw_token);

        if (!($token instanceof Plain)) {
            throw new Exception('Invalid token', 422);
        }

        if (!$token->claims()->has('user')) {
            throw new Exception('Invalid token', 401);
        }

        return $token;
    }

    /**
     * Retrieves the configuration for generating a JWT token
     *
     * @param string $signing_key The signing key used for generating the token.
     * @return Configuration The configuration object for generating the token.
     */
    public static function getConfig(): Configuration
    {
        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText(config('caronte.APP_SECRET'))
        );

        return $config;
    }

    /**
     * Retrieves the constraints for validating a JWT token.
     *
     * @param Configuration $config The configuration object.
     * @return array The array of validation constraints.
     */
    public static function getConstraints(): array
    {
        $constraints = [];

        if (config('caronte.ENFORCE_ISSUER')) {
            $constraints[] = new IssuedBy(config('caronte.ISSUER_ID'));
        }

        //!TODO VALIDATE SIGNING
        /* $constraints[] = new SignedWith(
            $config->signer(),
            $config->signingKey()
        );*/

        return $constraints;
    }



    /**
     * Remove the Caronte token from the cookie and local storage.
     *
     * @return void
     */


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
}
