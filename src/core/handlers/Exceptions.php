<?php

namespace SIMA\HANDLERS;

class Exceptions extends \Exception
{
    protected $message;
    protected $code;

    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->message = $message;
        $this->code = $code;
    }

    public function getCustomMessage()
    {
        return [
            'error' => true,
            'code' => $this->code,
            'message' => $this->message
        ];
    }

    public function toString()
    {
        return \Exception::__toString() . "\n" .
            "Error: [{$this->code}]: {$this->message}" . "\n";
    }
}
