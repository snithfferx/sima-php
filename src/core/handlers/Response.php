<?php
/**
 * Response Handler
 * @description This class is used to handle the response
 * @category Handler
 * @author Jorge Echeverria <jecheverria@bytes4run.com>
 * @package App
 * @license Bytes4Run 2025
 * @version 1.0.1
 * @link https://bytes4run.com
 * @copyright (c) 2021-2025 Bytes4Run
 */
declare (strict_types = 1);
namespace SIMA\HANDLERS;

class Response
{
    public static function sendResponse($code, $data)
    {
        http_response_code($code);
        echo json_encode($data);
        exit;
    }
}
