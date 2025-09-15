<?php
/**
 * Cors Helper
 * @description This class is used to handle the cors
 * @category Helper
 * @author snithfferx <jecheverria@bytes4run.com>
 * @package SIMA\HELPERS\Cors
 * @license Bytes4Run 2025
 * @version 1.0.1
 * @link https://bytes4run.com
 * @copyright (c) 2021-2025 Bytes4Run
 */
declare (strict_types = 1);
namespace SIMA\HELPERS;

class Cors
{
    private $allowedOrigins;

    public function __construct($configFile)
    {
        $this->allowedOrigins = $this->loadAllowedOrigins($configFile);
    }

    public function handleCors()
    {
        $origin = $_SERVER['HTTP_ORIGIN'];

        if ($this->isOriginAllowed($origin)) {
            header("Access-Control-Allow-Origin: $origin");
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
            header("Access-Control-Allow-Headers: Content-Type");
        } else {
            header("HTTP/1.1 403 Forbidden");
            exit;
        }
    }

    private function loadAllowedOrigins($configFile)
    {
        // Load the configuration file and extract the allowed origins
        // You can use any format for the configuration file (e.g., JSON, XML, INI)
        // Here's an example using a JSON file:
        $config = json_decode(file_get_contents($configFile), true);
        return $config['allowed_origins'];
    }

    private function isOriginAllowed($origin)
    {
        // Check if the origin is in the list of allowed origins
        return in_array($origin, $this->allowedOrigins);
    }

    function resolveOptions(string $origin) {
        // Check if the origin is in the list of allowed origins
        if (!$this->isOriginAllowed($origin)) {
            header("HTTP/1.1 403 Forbidden");
            exit;
        }
        header("Access-Control-Allow-Origin: {$origin}");
        header('Access-Control-Allow-Credentials: true');
        header("Access-Control-Allow-Methods: GET, DELETE, HEAD, OPTIONS, PATCH, POST, PUT");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
        header("Access-Control-Max-Age: 6400");
        header("Content-Length: 0");
        header("Content-Type: text/plain");
        http_response_code(204);
        exit;
    }
}
