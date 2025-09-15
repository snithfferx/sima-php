<?php
/**
 * Definer
 * @description Define global variables to use in the application
 * @category Helper
 * @author Jorge Echeverria <jecheverria@bytes4run.com>
 * @package HELPERS\Definer
 * @license Bytes4Run 2025
 * @version 1.0.1
 * @link https://bytes4run.com
 * @copyright (c) 2021-2025 Bytes4Run
 */
declare (strict_types = 1);
namespace SIMA\HELPERS;

class Definer
{
    public function __construct()
    {
        $this->define();
    }
    private function define(): void
    {
        define('ROOT_PATH', __DIR__ . '/../../');
        if (!defined("_APP_")) {
            define("_APP_", dirname(__FILE__, 3));
        }

        ##GLOBAL CLASS Core Variable
        if (!defined("_CLASS_")) {
            define("_CLASS_", _APP_ . "/core/classes/");
        }

        ##GLOBAL HELPER Core Variable
        if (!defined("_HELPER_")) {
            define("_HELPER_", _APP_ . "/core/helpers/");
        }

        ##GLOBAL MODULE Core Variable
        if (!defined("_MODULE_")) {
            define("_MODULE_", _APP_ . "/modules/");
        }

        ##GLOBAL VIEW Variable
        if (!defined("_VIEW_")) {
            define("_VIEW_", dirname(_APP_) . "/resources/views/");
        }
        ##GLOBAL CONFIGURATION Variable
        if (!defined("_CONF_")) {
            define("_CONF_", _APP_ . "/core/configs/");
        }

        if (!defined("_CACHE_")) {
            define("_CACHE_", dirname(_APP_) . "/cache/");
        }
        if (!defined("_ENT_")) {
            define("_ENT_", _APP_ . "/core/entities/");
        }
        if (!defined("_ASSETS_")) {
            define("_ASSETS_", dirname(_APP_) . "/public/");
        }

    }
}
