<?php
/**
 * Request Handler
 * @description This class is used to handle the request
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

class Request
{
    public static function getRemoteAddr()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    public static function getRequestUrl()
    {
        return $_SERVER['REQUEST_URI'];
    }

    public static function getRequestMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function getHttpReferer()
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    }

    public static function getHttpOrigin()
    {
        return isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    }

    public static function getHttpHost()
    {
        return $_SERVER['HTTP_HOST'];
    }

    public static function getHeaders()
    {
        return getallheaders();
    }

	public static function getHttpAccept()
	{
		return $_SERVER['HTTP_ACCEPT'];
	}

	public static function getHttpAcceptLanguage()
	{
		return $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	}

	public static function getContentType()
	{
		return $_SERVER['CONTENT_TYPE'];
	}

	public static function getContentLength()
	{
		return $_SERVER['CONTENT_LENGTH'];
	}

	public static function getRequestTime()
	{
		return $_SERVER['REQUEST_TIME'];
	}
}
