<?php
/**
 * Home
 * @description HomeController class for manage Home
 * @author Bytes4Run <info@bytes4run.com>
 * @category CONTROLLER
 * @package SIMA\MODULES\home\controllers\HomeController
 * @version 1.0.0
 * @date 2025-09-16
 * @time 07:40:07
 * @copyright (c) 2025 Bytes4Run
 */
# Strict types
declare (strict_types = 1);

namespace SIMA\MODULES\home\controllers;

use SIMA\CLASSES\Controller;

class HomeController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    public function index(): array
    {
        return $this->view('home/index', ['title' => 'Home']);
    }

	public function read(): array
    {
        return $this->index();
    }
}
