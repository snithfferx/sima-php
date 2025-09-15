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

use SIMA\HANDLERS\Request;
use SIMA\HANDLERS\Session;
use SIMA\HANDLERS\JWTHandler;

class Authentication {

    public function __construct(array $config,Request $request)
    {
        // If request is to login, skip authentication
        // echo $request::getRequestUrl(); exit();
        if ($request::getRequestUrl() == '/auth/login') {
            return;
        }
        if (!$this->isAuthenticated($request)) {
            $this->handleUnauthorized($config,$request);
        }
    }

    public static function isAuthenticated(Request $req)
    {
        // Check for valid session or JWT
        $sessionID = Session::getSessionID($req::getHeaders());
        $token = Session::getToken($req::getHeaders());
        return Session::isValid($sessionID) || JWTHandler::verify($token);
    }

    protected function handleUnauthorized(array $conf, Request $req)
    {
        if ($conf['engine'] == 'json') {
            // Handle JSON response
            header('Content-Type: application/json');
            http_response_code(401);
            return json_encode([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ]);
        }
        if ($this->isApiRequest($req)) {
            // Return JSON response for API requests
            header('Content-Type: application/json');
            http_response_code(401);
            return json_encode([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ]);
        }

        // Redirect to login page for web requests
        header('Location: /auth/login');
        exit();
    }

    protected function isApiRequest(Request $req)
    {
        return (
            strpos($req::getHttpAccept(), 'application/json') !== false
        ) || 
        (
            strpos($req::getContentType(), 'application/json') !== false
        );
    }

    static public function getAuth(Request $req)
    {
        // Check if user is authenticated
        if (self::isAuthenticated($req)) {
            return [
                'status' => 'success',
                'message' => 'User is authenticated',
                'data' => [
                    'user' => Session::getUser(),
                    'token' => JWTHandler::generate(Session::getUser())
                ]
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'User is not authenticated'
            ];
        }
    }
}
?>
