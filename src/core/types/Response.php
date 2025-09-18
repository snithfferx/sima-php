<?php

/**
 * Response type
 * @description Response type
 * @category Types
 * @author Jorge Echeverria <jecheverria@bytes4run.com>
 * @package TYPES
 * @license Bytes4Run 2025
 * @version 1.0.1
 * @link https://bytes4run.com
 * @copyright (c) 2021-2025 Bytes4Run
 */

declare (strict_types = 1);

namespace SIMA\TYPES;

class Response
{
    /**
     * @var string | int
     * Define the status of the response
     */
    private $status;
    /**
     * @var string
     * Define the message of the response
     */
    private $message;
    /**
     * @var array
     * Define the data of the response
     */
    private $data;

    function __construct(string | int $status = 200, string $message = '', array | null $data = [])
    {
        $this->status  = $status;
        $this->message = $message;
        $this->data    = $data;
    }

    function getMessage()
    {
        return $this->message;
    }
    function getStatus()
    {
        return $this->status;
    }
    function getData()
    {
        return $this->data;
    }
    function toArray()
    {
        return get_object_vars($this);
    }

    function json()
    {
        header('Content-Type: application/json'); //Especificamos el tipo de contenido a devolver
		$status = $this->getStatus();
		if (is_string($status) && is_numeric($status)) {
			$status = intval($status);
		}
		if (is_int($status) && $status < 100 || $status > 599) {
			$status = 500;
		}
        http_response_code($status);
		$data = $this->getData();
		if (!is_array($data)) {
			$data = [];
		}
		array_push($data, $this->getMessage());
        return json_encode($data, JSON_THROW_ON_ERROR); //Devolvemos el contenido
    }

    function get()
    {
        return $this;
    }

    function asError()
    {
        return new Error($this->status, $this->message);
    }
}
