<?php

/**
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.3.1
 * gruelas@gruelasjr
 *
 */

namespace Equidna\Caronte;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Equidna\Caronte\Facades\Caronte;
use Equidna\Toolkit\Exceptions\BadRequestException;
use Equidna\Toolkit\Exceptions\NotAcceptableException;
use Equidna\Toolkit\Exceptions\UnprocessableEntityException;
use Exception;

class CaronteToken
{
    public const MINIMUM_KEY_LENGTH = 32;

    private function __construct()
    {
        //
    }

    /**
     * Validate a JWT token string and return the validated token.
     *
     * @param string $raw_token Raw JWT token string.
     * @return Plain Validated token instance.
     * @throws NotAcceptableException|UnprocessableEntityException If the token is invalid or fails constraints.
     */
    public static function validateToken(string $raw_token): Plain
    {
        $config = static::getConfig();
        $token  = static::decodeToken(raw_token: $raw_token);

        try {
            $config->validator()->assert(
                $token,
                ...static::getConstraints()
            );
        } catch (RequiredConstraintsViolated $e) {
            throw new NotAcceptableException(
                'The token does not meet the required constraints: ' . $e->getMessage(),
                $e
            );
        }

        try {
            $config->validator()->assert(
                $token,
                new StrictValidAt(SystemClock::fromUTC())
            );

            if (config('caronte.UPDATE_LOCAL_USER')) {
                Caronte::updateUserData($token->claims()->get('user'));
            }

            return $token;
        } catch (RequiredConstraintsViolated $e) {
            return static::exchangeToken(raw_token: $raw_token);
        }
    }

    /**
     * Exchange a raw token for a validated token using the Caronte API.
     *
     * @param string $raw_token Raw JWT token string.
     * @return Plain Validated token instance.
     * @throws UnprocessableEntityException If the token exchange fails.
     */
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
            )->post(
                config('caronte.URL') . 'api/' . config('caronte.VERSION') . '/exchange',
                [
                    'app_id' => config('caronte.APP_ID')
                ]
            );

            if ($caronte_response->failed()) {
                throw new RequestException($caronte_response);
            }

            $token = static::validateToken($caronte_response->body());

            Caronte::saveToken($token->toString());
            Caronte::setTokenWasExchanged();

            return $token;
        } catch (RequestException $e) {
            Caronte::clearToken();
            throw new UnprocessableEntityException(
                'Cannot exchange token: ' . $e->getMessage(),
                $e
            );
        }
    }

    /**
     * Decode a raw JWT token string.
     *
     * @param string $raw_token Raw JWT token string.
     * @return Plain Decoded token instance.
     * @throws BadRequestException|UnprocessableEntityException If the token is missing, malformed, or invalid.
     */
    public static function decodeToken(string $raw_token): Plain
    {
        if (empty($raw_token)) {
            throw new BadRequestException('Token not provided');
        }

        if (count(explode(".", $raw_token)) != 3) {
            throw new BadRequestException('Malformed token');
        }

        $token = static::getConfig()->parser()->parse($raw_token);

        if (!($token instanceof Plain)) {
            throw new BadRequestException('Invalid token');
        }

        if (!$token->claims()->has('user')) {
            throw new UnprocessableEntityException('Invalid token');
        }

        return $token;
    }

    /**
     * Get the JWT configuration for token operations.
     *
     * @return Configuration JWT configuration instance.
     */
    public static function getConfig(): Configuration
    {
        $signing_key = (config('caronte.VERSION') == 'v1') ? config('caronte.TOKEN_KEY') : config('caronte.APP_SECRET');

        if (strlen($signing_key) < static::MINIMUM_KEY_LENGTH) {
            $signing_key = str_pad($signing_key, static::MINIMUM_KEY_LENGTH, "\0");
        }

        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($signing_key)
        );

        return $config;
    }

    /**
     * Get the constraints for validating a JWT token.
     *
     * @return array Array of validation constraints.
     */
    public static function getConstraints(): array
    {
        $constraints = [];

        $config = static::getConfig();

        if (config('caronte.ENFORCE_ISSUER')) {
            $constraints[] = new IssuedBy(config('caronte.ISSUER_ID'));
        }

        $constraints[] = new SignedWith(
            $config->signer(),
            $config->signingKey()
        );

        return $constraints;
    }
}
