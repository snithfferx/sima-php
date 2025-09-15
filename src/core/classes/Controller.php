<?php
/**
 * Controller Class to manage the application logic
 * @description This class is the base class for all controllers
 * @author Jorge Echeverria 
 * @category Class 
 * @package CLASSES\Controller
 * @version 1.7.0 
 * @date 2024-03-11 | 2025-07-29
 * @time 22:30:00
 * @copyright (c) 2024 - 2025 Bytes4Run 
 */
declare(strict_types=1);

namespace SIMA\CLASSES;

use Exception;

class Controller
{
    private ?array $error;
    private ?array $response;
    public function __construct()
    {
        $this->error = null;
        $this->response = null;
    }
    
    public function getError(): ?array {
        return $this->error;
    }

    public function getResponse(): ?array {
        return $this->response;
    }

    public function setError($error): void {
        if ($error instanceof Exception) {
            $this->error = ['code'=>$error->getCode(),'message' => $error->getMessage()];
        }
    }

    function getModuleResponse(string $module, string $controller, string $method, array | null $params)
    {
        $status = 404;
        $message = 'Method not found';
        $data = null;
        $component = $this->getComponent($module, $controller);
        if ($component instanceof Exception) {
            $this->response = ['code' => $component->getCode(), 'message' => $component->getMessage(), 'data' => $data];
        }
        if (method_exists($component, $method)) {
            try {
                $componentInitialized = [$component, $method];
                $params = ($params == null) ? [] : [$params];
                $data = call_user_func_array($componentInitialized, $params);
                $this->response = ['code' => 200, 'message' => 'ok', 'data' => $data];
            } catch (\Throwable $th) {
                $this->response = ['code' => $th->getCode(), 'message' => $th->getMessage(), 'data' => $data];
            }
        }
        $this->response = ['code' => $status, 'message' => $message, 'data' => $data];
        return $this;
    }
    
    protected function getController (string $name) {
        $splitName = explode("/", $name);
        if (sizeof($splitName) > 1) {
            $moduleName = $splitName[0];
            $controllerName = $splitName[1];
        } else {
            $moduleName = $splitName[0];
            $controllerName = $splitName[0];
        }
        return $this->getComponent($moduleName, "controller", $controllerName);
    }

    protected function getModel (string $name) {
        $splitName = explode("/", $name);
        if (sizeof($splitName) > 1) {
            $moduleName = $splitName[0];
            $modelName = $splitName[1];
        } else {
            $moduleName = $splitName[0];
            $modelName = $splitName[0];
        }
        return $this->getComponent($moduleName, "model", $modelName);
    }

    private function getComponent(string $moduleName, string $type = "controller", ?string $componentName = null): object {
        // $moduleName = ucfirst($moduleName);
        $module = ucfirst($moduleName);
        if (is_null($componentName)) {
            $componentName = $module;
        }
        $component = "MODULES\\";
        $component .= match ($type) {
            "model"   => $module . "\\models\\" . $componentName . "Model",
            default   => $module . "\\controllers\\" . $componentName . "Controller",
            "helper"  => $module . "\\helpers\\_" . $componentName . "Helper",
            "handler" => $module . "\\handlers\\__" . $componentName . "handler",
            "library" => $module . "\\libraries\\_" . $componentName . "_Library",
        };
        // $path = str_replace("/", "\\", $path);
        try {
            if (class_exists($component)) {
                try {
                    return new $component;
                } catch (Exception $th) {
                    return $th;
                }
            } else {
                throw new Exception("Component not found");
            }
        } catch (Exception $th) {
            return $th;
        }
    }
    /**
     * Crea una respuesta de tipo vista, para el helper view
     * @param string $name
     * @param array $content
     * @param array $breadcrumbs
     * @param string $type
     * @param string|int $code
     * @param array $style
     * @return array
     */
    protected function view(string $name, array $content = [], string $type = 'template', array $breadcrumbs = [], string | int $code = '', array $style = []): array
    {
        if (!empty($name)) {
            if (empty($breadcrumbs)) {
                $breadcrumbs = $this->createBreadcrumbs($name);
            }
        }
        return [
            'view' => [
                'type' => $type,
                'name' => $name,
                'data' => [
                    'code' => $code,
                    'style' => $style,
                ],
            ],
            'data' => [
                'breadcrumbs' => $breadcrumbs,
                'datos' => $content,
            ],
        ];
    }
    /**
     * Función que genera un arreglo de breadcrums.
     * @param string|array $values puede recibir una cade de caracteres con el nombre de la vista, ej.: "home/index"
     * o puede recibir un arreglo con los hijos de una vista, ej.:
     * ```php
     * $arreglo = [
     *  'view'=>"home/index",
     *  'children'=>[
     *    'main'=>"zapatos",
     *    'module'=>"accesorios",
     *    'method'=>"list",
     *    'params'=>null
     *   ]
     * ]
     * ```
     * @return array
     */
    protected function createBreadcrumbs(string | array $values): array
    {
        $routes = array();
        $mdl = 'home';
        $ctr = 'home';
        $mtd = 'index';
        $prm = null;
        if (is_string($values)) {
            $name = explode("/", $values);
            if (sizeof($name) > 2) {
                $mdl = $name[0];
                $ctr = $name[0];
                $mtd = $name[1];
                $prm = $name[2];
            } else {
                $mdl = $name[0];
                $ctr = $name[0];
                $mtd = "index";
            }
            array_push($routes, [
                'text' => $mdl,
                'param' => $prm,
                'method' => $mtd,
                'controller' => $ctr,
            ]);
        } else {
            if (isset($values['view'])) {
                $name = explode("/", $values['view']);
            }

            if (sizeof($name) > 1) {
                $mdl = $name[0];
                $ctr = $name[0];
            }
            foreach ($values['children'] as $child) {
                $mdl = ($child['main']) ?? $child['module'];
                $ctr = $child['module'];
                $mtd = $child['method'];
                if (isset($child['params'])) {
                    $prm = (is_array($child['params'])) 
                        ? implode("|", $child['params'])
                        : $child['params'];
                }
                array_push($routes, [
                    'text' => $mdl,
                    'param' => $prm,
                    'method' => $mtd,
                    'controller' => $ctr,
                ]);
            }
        }
        return [
            'main' => $mdl,
            'routes' => $routes,
        ];
    }
    /**
     * Function to generate a error message
     * @param string|int $type
     * @param mixed $content
     * @param string $display
     * @return array
     */
    public function error(string | int $type, mixed $content, string $display = "alert"): array {
        if (is_string($type)) {
            $type = match ($type) {
                "info" => 200,
                "error" => 500,
                "success" => 200,
                "warning" => 200,
                default => 200,
            };
        }
        if (is_array($content)) {
            $message = $content['message'];
            $code = $content['code'];
        } elseif (is_string($content)) {
            $message = $content;
            $code = $type;
        } elseif ($content instanceof Exception) {
            $message = $content->getMessage();
            $code = $content->getCode();
        }
        if ($display == "view" || $display == "template") {
            return [
                'view' => [
                    'type' => $display,
                    'name' => $code,
                    'data' => [
                        'code' => $code,
                        'style' => [
                            'title' => "Error " . $code,
                            'color' => "danger",
                        ],
                    ],
                ],
                'data' => [
                    'message' => $message,
                ],
            ];
        } else {
            return [
                'view' => [
                    'type' => 'json',
                    'name' => "error",
                ],
                'data' => [
                    'message' => $message,
                    'code' => $code,
                    'type' => $type,
                ],
            ];
        }
    }
    /**
     * Generates a JSON response based on the provided data array.
     *
     * @param array $data The data array containing the response data.
     * @return array The JSON response array with the response type and encoded data.
     */
    protected function json (array $data) {
        $code = (isset($data['status'])) ? $data['status'] : $data['data']['code'];
        http_response_code(intval($code));
        return array('json',json_encode($data, JSON_PRETTY_PRINT));
    }
    /** 
     * Function to Redirect to another page given
     * @param string $url
     */
    protected function redirect(string $url) {
        http_response_code(301);
        return array('redirect',$url);
    }
}

