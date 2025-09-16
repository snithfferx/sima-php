<?php
/** 
 * Auth
 * @description AuthController class for manage Auth
 * @author Bytes4Run <info@bytes4run.com>
 * @category CONTROLLER
 * @package SIMA\MODULES\Auth\controllers\AuthController
 * @version 1.0.0
 * @date 2025-09-15
 * @time 05:42:19
 * @copyright (c) 2025 Bytes4Run
 */
# Strict types
declare(strict_types=1);
# Namespace
namespace SIMA\MODULES\auth\controllers;
# Base
use SIMA\CLASSES\Controller;
use Throwable;
# Classes
use SIMA\MODULES\Auth\models\AuthModel;
use SIMA\HANDLERS\JWTHandler;
use SIMA\MIDDLEWARES\LoginAttemptMiddleware;
class AuthController extends Controller {
	private AuthModel $model;
    public function __construct(int $id = null) {
        $this->model = new AuthModel;
    }
    /**
     * @param array $params
     * @return array
     */
    public function login($params): array
    {
        LoginAttemptMiddleware::handle();

        $user = $this->model->findByEmail($params['email']);
        if (!$user || !password_verify($params['password'], $user['password'])) {
            LoginAttemptMiddleware::recordFailedLogin();
            return ['status' => 401, 'message' => 'Credenciales inválidas'];
        }

        LoginAttemptMiddleware::clearLoginAttempts();

        $token = JWTHandler::generate([
            'id' => $user['id'],
            'role' => $user['role'],
            'email' => $user['email']
        ]);

        JWTHandler::storeToken($token);

        return ['status' => 200, 'message' => 'Login exitoso', 'token' => $token];
    }
	public function register($params)
		{
			if (empty($params['email']) || empty($params['password'])) {
				return ['status' => 400, 'message' => 'Email y contraseña requeridos'];
			}

			if ($this->model->findByEmail($params['email'])) {
				return ['status' => 409, 'message' => 'El usuario ya existe'];
			}

			$hashed = password_hash($params['password'], PASSWORD_DEFAULT);
			$created = $this->model->create([
				'email' => $params['email'],
				'password' => $hashed,
				'role' => $params['role'] ?? 'vendedor',
				'active' => 1
			]);

			return ['status' => 201, 'message' => 'Usuario registrado', 'user_id' => $created];
		}
		/**
		 * @param array $params
		 * @return array
		 */
		public function logout(array $params): array
		{
			JWTHandler::invalidate();
			return ['status' => 200, 'message' => 'Logout exitoso'];
		}
	}
?>
