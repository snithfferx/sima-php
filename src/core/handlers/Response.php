<?php
/**
 * Response
 * @description This is the response class for the application
 * @author snithfferx <jecheverria@bytes4run.com>
 * @category Handler
 * @package MODULAR\CORE\HANDLERS
 * @version 1.0.0
 * Date: 2025-05-13
 * Time: 13:19:00
 * @copyright 2022 - 2025 bytes4run
 */
declare (strict_types = 1);
namespace SIMA\HANDLERS;

class Response
{
    private static $status;
    private static $message;
    private static $data;

    public function __construct(array|null $response = null)
    {
        self::$status  = $response['status'] ?? null;
        self::$message = $response['message'] ?? null;
        self::$data    = $response['data'] ?? null;
    }

    public static function getStatus()
    {
        return self::$status;
    }

    public static function getMessage()
    {
        return self::$message;
    }

    public static function getData()
    {
        return self::$data;
    }

    public static function toArray(): array
    {
        return [
            'status'  => self::$status,
            'message' => self::$message,
            'data'    => self::$data,
        ];
    }

    public static function toJson(): string
    {
        return json_encode(self::toArray());
    }

    public static function redirect(string $url): void
    {
        // Set the response header to redirect
        header('Location: ' . $url);
        exit();
    }

    public static function setStatus(int $status): void
    {
        self::$status = $status;
    }

    public static function setMessage(string $message): void
    {
        self::$message = $message;
    }

    public static function setData($data): void
    {
        self::$data = $data;
    }
}
