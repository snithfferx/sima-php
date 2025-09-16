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
        $callback = $this->callback['resolved'] ?? ['module' => 'home', 'controller' => 'home', 'method' => 'index'];
        $params = $this->params ?? [];

        if (empty($callback)) {
            $this->error = new Error('400', 'No callback function found');
            return false;
        }

        $module = $callback['module'];
        $method = $callback['method'];
        $controller = $callback['controller'];

        $result = $this->controller->getModuleResponse($module, $controller, $method, $params)->getResponse();

        if (!$result) {
            $this->error = new Error('400', 'No callback function found');
            return false;
        }

        $this->response = new Response($result['code'], $result['message'], $result['data']);
        return true;
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
       echo $data->json();
    }
}
