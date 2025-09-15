<?php
/**
 * Loader file
 * @description Application loader
 * @category Loader
 * @author Jorge Echeverria <jecheverria@bytes4run.com>
 * @package App
 * @license Bytes4Run 2025
 * @version 1.0.1
 * @link https://bytes4run.com
 * @copyright (c) 2021-2025 Bytes4Run
 */
declare (strict_types = 1);
namespace SIMA\bootstrap;

use SIMA\CLASSES\Controller;
use SIMA\HANDLERS\Router;
use SIMA\HELPERS\Definer;
use SIMA\HELPERS\Messenger;
use SIMA\TYPES\Error;
use SIMA\TYPES\Response;

class Loader
{
    private Messenger $messenger;
    private Router $router;
    public Response|null $response;
    public Error|null $error;
    private array|null $callback;
    private array|null $params;
    private Controller $controller;

    public function __construct()
    {
        new Definer();
        $this->messenger = new Messenger();
        $this->controller = new Controller();
        $this->response = null;
        $this->error = null;
        $this->router = new Router();
        // Getting request parameters
        $this->router->resolve();
        $this->callback = $this->router->getCallback();
        $this->params = $this->router->getParams();
    }

    public function run(): bool
    {
        $callback = !empty($this->callback) ? $this->callback['resolved'] : [];
        $params = !empty($this->params) ? $this->params : [];
        if (empty($callback)) {
            $callback = ['module'=>'home', 'controller'=>'home', 'method'=>'index'];
        }
        if ($callback) {
            $count = count($callback);
            if ($count > 0) {
                // if callback has only one data, it is a module, this will be the controller and method
                $module = $callback['module'];
                $method = $callback['method'];
                $controller = $callback['controller'];
                // if callback has two data, the first is the module and controller, and the second is the method
                // if ($count == 2) {
                //     $method = $callback['method'];
                // }
                // If callback has more than two data, the first is the module, the second is the controller, the third is the method
                // if ($count == 3) {
                //     $controller = $callback['controller'];
                //     $method = $callback['method'];
                // }
                $result = $this->controller->getModuleResponse($module, $controller, $method, $params)->getResponse();
				if (!$result) {
					$this->error = new Error('400', 'No callback function found');
					return false;
				}
                $this->response = new Response($result['code'], $result['message'], $result['data']);
                return true;
            }
        }
        $this->error = new Error('400', 'No callback function found');
        return false;
    }
    public function getResponse(): Response|null
    {
        return $this->response;
    }
    public function getError(): Error|null
    {
        return $this->error;
    }

    public function render(Response $data): void
    {
        // TODO: Implement render method as view
        // if ($data->getStatus() === 'ok' || $data->getStatus() === 200) {
        //     $this->view->render($data);
        // } else {
        //     $this->view->render($data);
        // }
       echo $data->json();
    }
    public function end()
    {
        if (isset($this->response)) {
            unset($this->response);
        }
        if (isset($this->error)) {
            unset($this->error);
        }
        if (isset($this->callback)) {
            unset($this->callback);
        }
        if (isset($this->params)) {
            unset($this->params);
        }
        if (isset($this->controller)) {
            unset($this->controller);
        }
        if (isset($this->router)) {
            unset($this->router);
        }
        if (isset($this->messenger)) {
            unset($this->messenger);
        }
    }
}
