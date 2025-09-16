<?php
/**
 * Connection Class to consume the server local database
 * @description This class is the base class for all database connections
 * @author Jorge Echeverria <jecheverria@bytes4run.com> 
 * @category Class 
 * @package CLASSES\Connection
 * @version 1.7.0 
 * @date 2024-03-11 | 2025-07-29
 * @time 22:30:00
 * @copyright (c) 2024 - 2025 Bytes4Run 
 */
declare(strict_types=1);

namespace SIMA\CLASSES;

use PDO;
use Exception;
use PDOException;

class Connection
{
    private string $host = "localhost";
    private string $db_name = "sima";
    private string $username = "root";
    private string $password = "";
    private string $charset = "utf8mb4";

    private string $dsn;
    private array $options;
    private PDO|null $pdo;
    private array|null $error;
    private array|null $response;

    public function __construct(string | null $dbName = null)
    {
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->db_name = $dbName ?? $_ENV['DB_DATABASE'] ?? 'sima';
        $this->username = $_ENV['DB_USERNAME'] ?? 'root';
        $this->password = $_ENV['DB_PASSWORD'] ?? '';
        $this->charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        try {
            $this->dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
            $this->options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->pdo = new PDO($this->dsn, $this->username, $this->password, $this->options);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function setError(array $error)
    {
        $this->error = $error;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function query(string $strStatement, array $arrParams = []): array
    {
        $this->getDbData($strStatement, $arrParams);
        return $this->response;
    }

    private function getDbData(string $strStatement, array $arrParams = []): void
    {
        if (!$this->pdo) {
            try {
                $this->pdo = new PDO($this->dsn, $this->username, $this->password, $this->options);
            } catch (PDOException $e) {
                throw new Exception("Database connection failed: " . $e->getMessage());
            }
        }
        try {
            $stmt = $this->pdo->prepare($strStatement);
            $stmt->execute($arrParams);

            if (str_starts_with(strtolower($strStatement), 'select')) {
                $this->response = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $this->response = ['affectedRows' => $stmt->rowCount(), 'insertId' => $this->pdo->lastInsertId()];
            }
        } catch (Exception $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }
}
