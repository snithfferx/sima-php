<?php
/** 
 * Auth
 * @description AuthModel class for manage Auth
 * @author Bytes4Run <info@bytes4run.com>
 * @category MODEL
 * @package SIMA\MODULES\Auth\models\AuthModel
 * @version 1.0.0
 * @date 2025-09-15
 * @time 05:15:16
 * @copyright (c) 2025 author_company 
 */
# Strict types
declare(strict_types=1);
# Namespace
namespace SIMA\MODULES\auth\models;
# Base
use SIMA\CLASSES\Model;
use Throwable;
# Classes
use SIMA\ENTITIES\Auth;
class AuthModel extends Model {
	protected array | null $error;
	protected array | null $response;
    public function __construct() {
        parent::__construct();
    }
    /** 
     * Function to set any error occurring on the Model
     * 
     * @param array $error
     * @return void
     */
    private function __setError(array $error): void {
       if (!is_null($this->error) && !empty($this->error)) {
           self::$error = array_merge(self::$error, $error);
       } else {
           self::$error = $error;
       }
    }
    /** 
     * Function to get the error from the Model
     * 
     * @return null|array
     * @throws \Exception
     */
    public function getError (): ?array {
        return $this->error;
    }
    /** 
     * Function to set any response occurring on the Model
     * 
     * @param array $response
     * @return void
     */
    protected function __setResponse(array $response): void {
       if (!is_null($this->response) && !empty($this->response)) {
           $this->response = array_merge($this->response, $response);
       } else {
           $this->response = $response;
       }
	}
	/** 
		* Function to get the response from the Model
		* 
		* @return null|array
		* @throws \Exception
		*/
	public function getResponse (): ?array {
		return $this->response;
	}
	/**
	 * @param array $params
	 * @return array
	 */
	public function findByEmail(array $params): array|null {
		$query = $this->select()->from('auth')->where('email = email')->get();
		if (is_null($query)) {
			$this->__setError($query->getError());
			return null;
		}
		return $query;
	}
	/**
	 * @param array $params
	 * @return array|null
	 */
	public function create(array $params): array|null {
		// Check if user already exists
		$user = $this->findByEmail($params);
		if (!is_null($user)) {
			$this->__setError(['error' => 'User already exists']);
			return null;
		}
		$query = $this->insert($params)->into('auth')->get();
		if (is_null($query)) {
			$this->__setError($query->getError());
			return null;
		}
		return $query;
	}
}
?>
