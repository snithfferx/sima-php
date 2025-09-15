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

 declare(strict_types=1);

 namespace SIMA\TYPES;

 class Response {
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

	 function __construct(string|int $status=200, string $message='', array|null $data=[]) {
		 $this->status = $status;
		 $this->message = $message;
		 $this->data = $data;
	 }
	 /* 
	 Array
(
    [code] => 200
    [message] => ok
    [data] => Array
        (
            [view] => Array
                (
                    [type] => view
                    [name] => home/index
                )

            [data] => Array
                (
                    [breadcrumbs] => Array
                        (
                            [main] => home
                            [routes] => Array
                                (
                                    [0] => Array
                                        (
                                            [text] => home
                                            [param] => 
                                            [method] => index
                                            [controller] => index
                                        )

                                )

                        )

                    [datos] => Array
                        (
                        )

                )

        )

) */

	 function getMessage() {
		 return $this->message;
	 }
	 function getStatus() {
		 return $this->status;
	 }
	 function getData() {
		 return $this->data;
	 }
	 function toArray() {
		 return get_object_vars($this);
	 }

	 function json() {
		 return json_encode($this);
	 }

	 function get() {
		 return $this;
	 }

	 function asError() {
		 return new Error($this->status,$this->message);
	 }
 }

?>
