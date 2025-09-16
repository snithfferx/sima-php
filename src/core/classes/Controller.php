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
declare (strict_types = 1);

namespace SIMA\CLASSES;

use Exception;
use SIMA\MIDDLEWARES\Auth;

class Controller
{
    private ?array $error;
    private ?array $response;
    public function __construct()
    {
        $this->error    = null;
        $this->response = null;
    }

    public function getError(): ?array
    {
        return $this->error;
    }

    public function getResponse(): ?array
    {
        return $this->response;
    }

    public function setError($error): void
    {
        if ($error instanceof Exception) {
            $this->error = ['code' => $error->getCode(), 'message' => $error->getMessage()];
        }
    }

    public function getModuleResponse(string $module, string $controller, string $method, array | null $params)
    {
        $component = $this->getComponent($module, $controller);

        if ($component instanceof Exception) {
            $this->response = ['code' => $component->getCode(), 'message' => $component->getMessage(), 'data' => null];
            return $this;
        }

        if (method_exists($component, $method)) {
            try {
                if (property_exists($component, 'access') && isset($component::$access[$method])) {
                    $rolesPermitidos = $component::$access[$method];
                    if (! Auth::checkRole($rolesPermitidos)) {
                        $this->response = ['code' => 403, 'message' => 'Acceso denegado', 'data' => null];
                        return $this;
                    }
                }
                $componentInitialized = [$component, $method];
                $params               = ($params == null) ? [] : [$params];
                $data                 = call_user_func_array($componentInitialized, $params);
                $this->response       = ['code' => 200, 'message' => 'ok', 'data' => $data];
            } catch (\Throwable $th) {
                $this->response = ['code' => $th->getCode(), 'message' => $th->getMessage(), 'data' => null];
            }
        } else {
            $this->response = ['code' => 404, 'message' => 'Method not found', 'data' => null];
        }
        return $this;
    }

    protected function getController(string $name)
    {
        return $this->getComponent($name, "controller");
    }

    protected function getModel(string $name)
    {
        return $this->getComponent($name, "model");
    }

    private function getComponent(string $name, string $type = "controller"): object
    {
        $splitName = explode("/", $name);
        if (sizeof($splitName) > 1) {
            $moduleName = $splitName[0];
            $componentName = $splitName[1];
        } else {
            $moduleName = $splitName[0];
            $componentName = $splitName[0];
        }

        $module = ucfirst($moduleName);
        $componentName = ucfirst($componentName);

        $component = "SIMA\\MODULES\\";
        $component .= match ($type) {
            "model"   => $module . "\\models\\" . $componentName . "Model",
            "helper"  => $module . "\\helpers\\_" . $componentName . "Helper",
            "handler" => $module . "\\handlers\\__" . $componentName . "handler",
            "library" => $module . "\\libraries\\_" . $componentName . "_Library",
            default   => $module . "\\controllers\\" . $componentName . "Controller",
        };

        try {
            if (class_exists($component)) {
                return new $component;
            } else {
                throw new Exception("Component not found: ". $component);
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
        if (! empty($name)) {
            if (empty($breadcrumbs)) {
                $breadcrumbs = $this->createBreadcrumbs($name);
            }
        }
        return [
            'view' => [
                'type' => $type,
                'name' => $name,
                'data' => [
                    'code'  => $code,
                    'style' => $style,
                ],
            ],
            'data' => [
                'breadcrumbs' => $breadcrumbs,
                'datos'       => $content,
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
        $routes = [];
        $mdl    = 'home';
        $ctr    = 'home';
        $mtd    = 'index';
        $prm    = null;
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
                'text'       => $mdl,
                'param'      => $prm,
                'method'     => $mtd,
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
                    'text'       => $mdl,
                    'param'      => $prm,
                    'method'     => $mtd,
                    'controller' => $ctr,
                ]);
            }
        }
        return [
            'main'   => $mdl,
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
    public function error(string | int $type, mixed $content, string $display = "alert"): array
    {
        if (is_string($type)) {
            $type = match ($type) {
                "info"    => 200,
                "error"   => 500,
                "success" => 200,
                "warning" => 200,
                default   => 200,
            };
        }
        if (is_array($content)) {
            $message = $content['message'];
            $code    = $content['code'];
        } elseif (is_string($content)) {
            $message = $content;
            $code    = $type;
        } elseif ($content instanceof Exception) {
            $message = $content->getMessage();
            $code    = $content->getCode();
        }
        if ($display == "view" || $display == "template") {
            return [
                'view' => [
                    'type' => $display,
                    'name' => $code,
                    'data' => [
                        'code'  => $code,
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
                    'code'    => $code,
                    'type'    => $type,
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
    protected function json(array $data, int $code = 200)
    {
        return [
            'code' => $code,
            'message' => 'ok',
            'data' => $data,
            'type' => 'json'
        ];
    }

    /**
     * Function to Redirect to another page given
     * @param string $url
     */
    protected function redirect(string $url)
    {
        return [
            'code' => 301,
            'message' => 'redirect',
            'data' => $url,
            'type' => 'redirect'
        ];
    }
}
