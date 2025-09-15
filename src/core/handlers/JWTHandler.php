<?php
/**
 * JWT Handler
 * @description This class is used to handle the JWT
 * @category Handler
 * @author Jorge Echeverria <jecheverria@bytes4run.com>
 * @package SIMA\HANDLERS
 * @license Bytes4Run 2025
 * @version 1.0.1
 * @link https://bytes4run.com
 * @copyright (c) 2021-2025 Bytes4Run
 */
declare (strict_types = 1);
namespace SIMA\HANDLERS;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHandler
{
    private static string $secret;
    private static string $algo = 'HS256';
    private static int $expiry = 3600; // 1 hora

    public static function init(): void
    {
        self::$secret = getenv('JWT_SECRET') ?: 'default_secret';
    }

    public static function generate(array $payload): string
    {
        self::init();
        $payload['iat'] = time();
        $payload['exp'] = time() + self::$expiry;

        return JWT::encode($payload, self::$secret, self::$algo);
    }

    public static function verify(string $token): ?array
    {
        self::init();
        try {
            return (array) JWT::decode($token, new Key(self::$secret, self::$algo));
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function storeToken(string $token): void
    {
        setcookie('auth_token', $token, time() + self::$expiry, '/', '', false, true);
        $_SESSION['auth_token'] = $token;
    }

    public static function getToken(): ?string
    {
        return $_SESSION['auth_token'] ?? $_COOKIE['auth_token'] ?? null;
    }

    public static function invalidate(): void
    {
        unset($_SESSION['auth_token']);
        setcookie('auth_token', '', time() - 3600, '/', '', false, true);
    }
}
