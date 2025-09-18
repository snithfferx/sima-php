<?php
/**
 * Router Handler
 * @description This class is used to handle the router
 * @category Handler
 * @author Jorge Echeverria <jecheverria@bytes4run.com>
 * @package SIMA\HANDLERS
 * @license Bytes4Run 2025
 * @version 1.0.1
 * @link https://bytes4run.com
 * @copyright (c) 2021-2025 Bytes4Run
 */
declare (strict_types = 1);
namespace SIMA\HANDLERS;
use SIMA\HANDLERS\Request;

class Router
{
    private Request $request;

    private array|null $params;
    private array|null $callback;
    private string $url;
    private bool $isQuery = false;
    public function __construct()
    {
        $this->params = null;
        $this->callback = null;
        $this->request = new Request();
    }
    public function resolve()
    {
        $path = $this->getPath();
        $method = $this->getMethod();
        $this->callback = [
            'origin' => $path,
            'method' => $method,
        ];
        if (!empty($path)) {
            if ($this->isQuery) {
                if (preg_match('/\//', $path[0])) {
                    $slashed = explode('/', $path[0]);
                    // loop through slashed array and find if each element is not empty
                    $valid = [];
                    foreach ($slashed as $key => $value) {
                        if (!empty($value)) {
                            array_push($valid, $value);
                        }
                    }
					unset($value);
                    $this->callback['request'][0] = $valid;
                }
                $this->params = $this->createParams($path[1]);
            } else {
                $valid = [];
                foreach ($this->callback['origin'] as $key => $value) {
                    if (!empty($value)) {
                        array_push($valid, $value);
                    }
                }
                $this->callback['request'] = $valid;
            }
        }
        match ($method) {
            "option" => $this->corsResolver(),
            "get" => $this->resolveGet(),
            "post" => $this->resolvePost(),
            "put" => $this->resolvePut(),
            "patch" => $this->resolvePatch(),
            "delete" => $this->resolveDelete(),
            default => $this->resolveCallback($method),
        };
    }
    public function getCallback()
    {
        return $this->callback;
    }

    public function getParams()
    {
        return $this->params;
    }

    private function getPath(): array
    {
        $url = $this->request::getRequestUrl();
        if ($url === null) {
            return [];
        }
        $url = ($url == "/" || $url == "/index.php" || $url == "/index.html") ? "" : substr($url, 1);
        $this->url = $url;
        if (preg_match('/\?/', $url)) {
            $this->isQuery = true;
            $res = preg_split('/\?/', $url, -1, PREG_SPLIT_NO_EMPTY);
            return $res && !empty($res) ? $res : [];
        } else {
            return empty($url) ? [] : explode("/", $url);
        }
    }

    /**
     * Función para obtener el método de la petición
     * @return string
     */
    public function getMethod(): string
    {
        return strtolower($this->request::getRequestMethod());
    }

    private function resolveStaticContent(array $path)
    {
        $filePath = dirname(_APP_);
        $file = implode('/', $path);
        if (!file_exists($filePath . $file)) {
            http_response_code(404);
            exit;
        }
        $mime = $this->getMIME(end($path));
        header("Content-Type: " . $mime);
        include $filePath . $file;
        exit;
    }

    private function createParams(string $uriParams): array
    {
        if (empty($uriParams)) {
            return array();
        }

        $uriArray = preg_split('/\&/', $uriParams, -1, PREG_SPLIT_NO_EMPTY);
        $uriParameters = array();
        foreach ($uriArray as $param) {
            $parameter = preg_split('/\=/', $param, -1, PREG_SPLIT_NO_EMPTY);
            $uriParameters[$parameter[0]] = isset($parameter[1]) ? $parameter[1] : null;
        }
        return (!empty($uriParameters)) ? $uriParameters : array();
    }

    private function getDefaults(array $url = [], string $method = 'get')
    {
        $mdl = "home";
        $ctr = $mdl;
        $mtd = match ($method) {
            "post" => "create",
            "get" => "read",
            "put" => "update",
            "delete" => "delete",
            "patch" => "edit",
            default => "index"
        };
        if (empty($url)) {
            return ['module' => $mdl, 'controller' => $ctr, 'method' => $mtd];
        } else {
            $count = count($url);
            return match ($count) {
                1 => ['module' => $url[0], 'controller' => $url[0], 'method' => $mtd],
                2 => ['module' => $url[0], 'controller' => $url[0], 'method' => $url[1]],
                3 => ['module' => $url[0], 'controller' => $url[1], 'method' => $url[2]],
                default => ['module' => $mdl, 'controller' => $ctr, 'method' => $mtd]
            };
        }
    }
    /**
     * Función para obtener el MIME del archivo solicitado
     * @return string
     */
    public function getMIME(string $asset): string
    {
        $nameSplited = explode('.', $asset);
        $extension = end($nameSplited);
        $mime = match ($extension) {
            "js" => "text/javascript",
            "css" => "text/css",
            "png" => "image/png",
            "jpg" => "image/jpeg",
            "gif" => "image/gif",
            "svg" => "image/svg+xml",
            "ico" => "image/x-icon",
            "jpeg" => "image/jpeg",
            default => "text/plain"
        };
        return $mime;
    }

    private function corsResolver(): void
    {
        // filter origins from file
        $origin = $this->request::getHttpOrigin();
        // $originsAllowed = $this->config->get('cors', 'json'); Origin is allowed from config
        header("Access-Control-Allow-Origin: {$origin}");
        header('Access-Control-Allow-Credentials: true');
        header("Access-Control-Allow-Methods: GET, DELETE, HEAD, OPTIONS, PATCH, POST, PUT");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
        header("Access-Control-Max-Age: 6400");
        header("Content-Length: 0");
        header("Content-Type: text/plain");
        http_response_code(204);
        exit;
    }

    private function resolveGet(): void
    {
        if (empty($this->callback['origin'])) {
            $this->callback['resolved'] = $this->getDefaults();
        }
        $count = 0;
        $count = $this->isQuery
        ? count($this->callback['request'][0])
        : count($this->callback['origin']);
        /* GET
        http://modular.test/products?details=23(module/parameter)
        http://modular.test/products/details?id=23(module/parameter)
        http://modular.test/products/store/details?id=23(module/controller/method/params)
        http://modular.test/products(module)
        http://modular.test/products/list(module/method)
        http://modular.test/products/details/23(module/method/parmas)
        http://modular.test/products/products/details/23(module/controller/method/params)
         */

        if (isset($this->callback['origin'][0]) && in_array($this->callback['origin'][0], ["assets", "css", "js", "img"])) {
            $this->resolveStaticContent($this->callback['origin']);
            exit;
        }
        # Resolve slashed URL
        /*  CALLBACK can be like:
        $this->callback = Array
        (
        [origin] => Array
        (
        )

        [method] => get
        [resolved] => Array
        (
        [module] => home
        [controller] => home
        [method] => read
        )
        )
         */
        // If $this->callback has key 0, resolve the URL
        // Key 0 comes only if the URL has query string parameters
        if (isset($this->callback['origin'])) {
            ## /module/controller/method..
            if ($count > 3) {
                ## /module/controller/method/params...
                for ($x = 3; $x < $count; $x++) {
                    $this->params[] = $this->callback['origin'][$x];
                }
                $this->callback['resolved'] = [
                    'module' => $this->callback['origin'][0],
                    'controller' => $this->callback['origin'][1],
                    'method' => $this->callback['origin'][2],
                ];
            } else {
                ## /module?params...
                ## /module/controller?params...
                ## /module/controller/method?params...
                ## /module/(module)
                ## /module/method (controller and method)
                ## /module/controller/method (module, controller and method)
                if ($this->isQuery) {
                    $this->callback['resolved'] = ($count == 0 || $count == 1)
                    ? $this->getDefaults([$this->callback['origin'][0]], 'get')
                    : $this->getDefaults($this->callback['request'][0], 'get');
                } else {
                    $this->callback['resolved'] = $this->getDefaults($this->callback['origin'], 'get');
                }
                // $this->params = $this->createParams($this->callback['origin'][1]);
            }
        } else {
            ## /module?params...
            $this->callback['resolved'] = $this->getDefaults([$this->callback['origin'][0]], 'get');
        }
    }

    private function resolvePost(): void
    {
        if (empty($this->callback['origin'])) {
            $this->callback = $this->getDefaults();
        }

        $this->params = (!empty($_POST)) ? $_POST : [];
        if (empty($this->params)) {
            $result = json_decode(file_get_contents('php://input'), true);
            $this->params = ($result !== false || $result !== null) ? $result : [];
        }
    }

    private function resolvePut(): void
    {
        $this->params = (!empty($_PUT)) ? $_PUT : [];
        if (empty($this->params)) {
            $result = [];
            parse_str(file_get_contents('php://input'), $result);
            $this->params = ($result !== false || $result !== null) ? $result : [];
        }
    }

    private function resolvePatch(): void
    {
        $this->params = (!empty($_PATCH)) ? $_PATCH : [];
        if (empty($this->params)) {
            $result = [];
            parse_str(file_get_contents('php://input'), $result);
            $this->params = ($result !== false || $result !== null) ? $result : [];
        }
    }

    private function resolveDelete(): void
    {
        $this->params = (!empty($_DELETE)) ? $_DELETE : [];
        if (empty($this->params)) {
            $result = [];
            parse_str(file_get_contents('php://input'), $result);
            $this->params = ($result !== false || $result !== null) ? $result : [];
        }
    }
    private function resolveCallback(string $method): void
    {
        $this->callback['resolved'] = $this->getDefaults();
        if (empty($this->callback['origin'])) {
            $this->callback['resolved'] = $this->getDefaults();
            exit;
        }
        if (isset($this->callback['origin'][0])) {
            $this->callback['resolved'] = $this->getDefaults($this->callback['origin'], $method);
        }
    }
}
