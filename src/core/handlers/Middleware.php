<?php
/**
 * Class to handle middleware
 * @description Clase que maneja los middleware
 * @category Handler
 * @package MODULAR\CORE\HANDLERS
 * @author snithfferx <jecheverria@bytes4run.com>
 * @version 1.0.0
 * @date 2024-01-10
 * @time 16:00:00
 * @copyright 2022 - 2025 Byest4Run
 */
declare (strict_types = 1);

namespace SIMA\HANDLERS;

use SIMA\HANDLERS\Authentication;
use SIMA\HANDLERS\Request;
use SIMA\HANDLERS\Response;
use SIMA\HELPERS\Configs;

class Middleware
{
    protected Request $Request;
    protected array|null $Response;
    private Authentication $Auth;
    private Configs $Config;
    public function __construct(Configs $Config, Request $request)
    {
        // Load the request
        $this->Request = $request;
        // Load the configuration file
        $cnf = $Config->get();
        // Load the authorization
        $this->Auth = new Authentication($cnf, $request);
    }

    public function isAuthenticated()
    {
        // Check if user is authenticated
        if ($this->Auth::isAuthenticated($this->Request)) {
            $this->Response = $this->Auth::getAuth($this->Request);
            return true;
        } else {
            // redirect to login page
            Response::redirect('auth/login');
            // return false;
        }
    }
}
