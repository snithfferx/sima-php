<?php
/**
 * Auth middleware
 * @description This class is used to initialize and configure the engine to render the views
 * @category middleware
 * @author Jorge Echeverria <jecheverria@bytes4run.com>
 * @package SIMA\MIDDLEWARES\Auth
 * @version 1.0.0
 * @date 2024-03-06 - 2025-04-03
 * @time 11:00:00
 * @copyright (c) 2022-2025 Bytes4Run
 */

declare (strict_types = 1);
namespace SIMA\MIDDLEWARES;

use SIMA\HANDLERS\JWTHandler;

class Auth
{
    private static array $user = [];

    public static function check(): bool
    {
        $token = JWTHandler::getToken();
        $payload = $token ? JWTHandler::verify($token) : null;

        if (!$payload || !isset($payload['id'], $payload['role'])) {
            return false;
        }

        self::$user = $payload;
        return true;
    }

    public static function checkRole(string|array $roles): bool
    {
        if (!self::check()) return false;

        $userRole = self::$user['role'] ?? null;

        if (is_array($roles)) {
            return in_array($userRole, $roles);
        }

        return $userRole === $roles;
    }

    public static function user(): array
    {
        return self::$user;
    }
}
