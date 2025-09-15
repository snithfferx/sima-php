<?php
/**
 * Error type
 * @description Error type
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
class Error
{
    /**
     * @var string | null
     * Define the error code of the response
     */
    private string|null $code;
    /**
     * @var string | null
     * Define the error message of the response
     */
    private string|null $message;
    public function __construct(string | int $code = 0, string | null $message = null)
    {
        $this->code    = $code;
        $this->message = $message;
    }
}
