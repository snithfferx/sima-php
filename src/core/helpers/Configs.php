<?php
/**
 * Configs Helper
 * @description This class is used to handle the configs
 * @category Helper
 * @author Jorge Echeverria <jecheverria@bytes4run.com>
 * @package HELPERS\Configs
 * @license Bytes4Run 2025
 * @version 1.0.1
 * @link https://bytes4run.com
 * @copyright (c) 2021-2025 Bytes4Run
 */
declare (strict_types = 1);
namespace SIMA\HELPERS;

use Dotenv\Dotenv;
use Exception;

class Configs
{
    public function __construct(string $file = "default", string $type = 'env')
    {
        $this->getConfigVars($file, $type);
    }
    public function get(string $value = "default", string $type = 'env'): array
    {
        return $this->getConfigVars($value, $type);
    }
    private function getConfigVars(string $file, string $type): array
    {
        $conf = [];
        try {
            if ($type == "json") {
                $path = _CONF_ . $file . ".json";
                $conf = json_decode(file_get_contents($path), true);
            } else {
                if (!empty($file) && $file !== "default") {
                    $dotenv = Dotenv::createImmutable(_CONF_, $file . ".env");
                } else {
                    $dotenv = Dotenv::createImmutable(_CONF_);
                }
                $dotenv->safeLoad();
            }
        } catch (Exception $ex) {
            return ['type' => "error", 'data' => $ex];
        }
        return $conf;
    }
    private function setConfigVars(string $fileName, array $fileData): bool
    {
        $result = false;
        if (file_exists(_CONF_ . $fileName . ".json")) {
            $conf = json_decode(file_get_contents(_CONF_ . $fileName . ".json"), true);
            $conf = array_merge($conf, $fileData);
            $result = file_put_contents(_CONF_ . $fileName . ".json", json_encode($conf));
        } else {
            $result = file_put_contents(_CONF_ . $fileName . ".json", json_encode($fileData));
        }
        return (bool) $result;
    }
}
