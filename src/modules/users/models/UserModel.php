<?php

/**
 * User
 * @description UserModel class for manage User
 * @author Bytes4Run <info@bytes4run.com>
 * @category MODEL
 * @package SIMA\MODULES\User\models\UserModel
 * @version 1.0.0
 * @date 2025-09-16
 * @time 00:29:20
 * @copyright (c) 2025 Bytes4Run
 */
# Strict types
declare (strict_types = 1);
# Namespace
namespace SIMA\MODULES\users\models;

# Base
use SIMA\CLASSES\Model;
# Classes
use SIMA\ENTITIES\User;

class UserModel extends Model
{
    private array|null $error;
    private array|null $response;
    private User|null $user;
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Function to set any error occurring on the Model
     *
     * @param array $error
     * @return void
     */
    private function __setError(array $error): void
    {
        if (! is_null($this->error) && ! empty($this->error)) {
            $this->error = array_merge($this->error, $error);
        } else {
            $this->error = $error;
        }
    }
    /**
     * Function to get the error from the Model
     *
     * @return null|array
     * @throws \Exception
     */
    public function getError(): ?array
    {
        return $this->error;
    }
    /**
     * Function to set any response occurring on the Model
     *
     * @param array $response
     * @return void
     */
    private function __setResponse(array $response): void
    {
        if (! is_null($this->response) && ! empty($this->response)) {
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
    public function getResponse(): ?array
    {
        return $this->response;
    }
    /**
     * Function to get the user from the Model
     *
     * @return null|User
     * @throws \Exception
     */
    public function getUser(int $id): ?User
    {
        $user = $this->select()->from('users')->where('id = ' . $id)->get();
        if ($user && ! empty($user)) {
            $this->user = $user[0];
        } else {
            $this->__setError(['error' => 'User not found']);
            $this->__setResponse(['response' => 'User not found']);
            return null;
        }
        return $this->user;
    }
    public function getAll(
        string | null $terms = null,
        int $page = 1,
        int $limit = 10,
        string $order = 'asc',
        string $orderby = 'id'): ?array {
        $offset = ($page - 1) * $limit;
        if ($terms && ! empty($terms)) {
            $users = $this->select()
                ->from('users')
                ->where('name LIKE %' . $terms . '%')
                ->limit($limit)
                ->offset($offset)
                ->orderBy($order, $orderby)
                ->get();
        } else {
            $users = $this->select()
                ->from('users')
                ->limit($limit)
                ->offset($offset)
                ->orderBy($order, $orderby)
                ->get();
        }
        if ($users && ! empty($users)) {
            return $users;
        } else {
            $this->__setError(['error' => 'Users not found']);
            $this->__setResponse(['response' => 'Users not found']);
            return null;
        }
    }
    public function getByEmail(string $email): ?User
    {
        $user = $this->select()
            ->from('users')
            ->where('email = ' . $email)
            ->get();
        if ($user && ! empty($user)) {
            $this->user = $user[0];
        } else {
            $this->__setError(['error' => 'User not found']);
            $this->__setResponse(['response' => 'User not found']);
            return null;
        }
        return $this->user;
    }
    public function getByUsername(string $username): ?User
    {
        $user = $this->select()
            ->from('users')
            ->where('username = ' . $username)
            ->get();
        if ($user && ! empty($user)) {
            $this->user = $user[0];
        } else {
            $this->__setError(['error' => 'User not found']);
            $this->__setResponse(['response' => 'User not found']);
            return null;
        }
        return $this->user;
    }
}
