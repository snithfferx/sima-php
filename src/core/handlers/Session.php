<?php
/**
 * Clase que será el corredor entre el usuario y la aplicación.
 * @description This class is the broker between users and aplication.
 * @category handler
 * @package SIMA\HANDLERS
 * @author snithfferx <jecheverria@bytes4run.com>
 * @version 1.0.0
 * Time: 2023-16-04 13:19:00
 * Date: 2023-16-04
 * @copyright 2022 - 2025 Byest4Run
 */
declare (strict_types = 1);

namespace SIMA\HANDLERS;

class Session
{
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set($key, $value)
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function destroy()
    {
        self::start();
        session_destroy();
        $_SESSION = [];
    }

    public static function isValid($sessionID)
    {
        self::start();
        return isset($_SESSION[$sessionID]) && ! empty($_SESSION[$sessionID]);
    }

    public static function regenerate()
    {
        self::start();
        session_regenerate_id(true);
    }

    public static function setUser($userData)
    {
        self::start();
        self::set('user_id', $userData['id']);
        self::set('user_data', $userData);
        self::regenerate();
    }

    public static function getUser()
    {
        return self::get('user_data');
    }

    public static function getSessionID($headers)
    {
        if (isset($headers['Session-ID'])) {
            return $headers['Session-ID'];
        }
        return null;
    }
    public static function getToken($headers)
    {
        if (isset($headers['Authorization'])) {
            $authentication = str_replace('Bearer ', '', $headers['Authorization']);
            return is_string($authentication) ? $authentication : null;
        }
        return null;
    }
}
