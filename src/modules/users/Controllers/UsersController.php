<?php
/**
 * User
 * @description UserController class for manage User
 * @author Bytes4Run <info@bytes4run.com>
 * @category CONTROLLER
 * @package SIMA\MODULES\users\controllers\UserController
 * @version 1.0.0
 * @date 2025-09-16
 * @time 00:29:20
 * @copyright (c) 2025 Bytes4Run
 */
# Strict types
declare (strict_types = 1);
# Namespace
namespace SIMA\MODULES\users\controllers;

# Base
use SIMA\CLASSES\Controller;
use SIMA\MODULES\users\models\UserModel;
# Classes

class UsersController extends Controller
{
    protected array $access = [
        'readOne'        => ['admin', 'user'],
        'readAll'        => ['admin'],
        'readByEmail'    => ['admin', 'user'],
        'readByUsername' => ['admin', 'user'],
        'readByTerms'    => ['admin', 'user'],
    ];
    protected array $params = [];
    private UserModel $model;
    public function __construct()
    {
        $this->model = new UserModel;
    }
    public function readOne(array $data)
    {
        $this->model->getUser(intval($data['id']));
    }
    public function read()
    {
        $this->model->getAll();
    }
    public function readByEmail(string $email)
    {
        $this->model->getByEmail($email);
    }
    public function readByUsername(string $username)
    {
        $this->model->getByUsername($username);
    }
    public function search(string $terms, int $page = 1, int $limit = 10, string $order = 'asc', string $orderby = 'id')
    {
        $this->model->getAll($terms, $page, $limit, $order, $orderby);
    }
}
