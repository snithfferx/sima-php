<?php
/**
 * LoginAttemptMiddleware middleware
 * @description This class is used to initialize and configure the engine to render the views
 * @category middleware
 * @author Jorge Echeverria <jecheverria@bytes4run.com>
 * @package SIMA\MIDDLEWARES\LoginAttemptMiddleware
 * @version 1.0.0
 * @date 2024-03-06 - 2025-04-03
 * @time 11:00:00
 * @copyright (c) 2022-2025 Bytes4Run
 */

declare (strict_types = 1);
namespace SIMA\MIDDLEWARES;

use SIMA\HELPERS\Messenger;
use SIMA\HANDLERS\Response;

class LoginAttemptMiddleware
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_TIME = 300; // 5 minutes

    public static function handle()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = [];
        }

        $ip = $_SERVER['REMOTE_ADDR'];
        $now = time();

        if (!isset($_SESSION['login_attempts'][$ip])) {
            $_SESSION['login_attempts'][$ip] = [
                'attempts' => 0,
                'time' => $now,
            ];
        }

        $attemptsInfo = &$_SESSION['login_attempts'][$ip];

        if ($now - $attemptsInfo['time'] > self::LOCKOUT_TIME) {
            // Reset attempts after lockout time
            $attemptsInfo['attempts'] = 0;
            $attemptsInfo['time'] = $now;
        }

        if ($attemptsInfo['attempts'] >= self::MAX_ATTEMPTS) {
            $response = new Response([
                'status' => 403,
                'message' => Messenger::json(['status'=>'error','code'=>403,'message'=>'login_attempts_exceeded']),
            ]);
            return $response->toArray();
        }
    }

    public static function recordFailedLogin()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $ip = $_SERVER['REMOTE_ADDR'];
        $now = time();

        if (!isset($_SESSION['login_attempts'][$ip])) {
            $_SESSION['login_attempts'][$ip] = [
                'attempts' => 0,
                'time' => $now,
            ];
        }
        
        $_SESSION['login_attempts'][$ip]['attempts']++;
        $_SESSION['login_attempts'][$ip]['time'] = $now;
    }

    public static function clearLoginAttempts()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_SESSION['login_attempts'][$ip])) {
            unset($_SESSION['login_attempts'][$ip]);
        }
    }
}
