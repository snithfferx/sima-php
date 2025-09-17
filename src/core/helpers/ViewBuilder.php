<?php
/**
 * ViewBuilder
 * @description This class is used to build the views of the application
 * @category Helper
 * @author Jorge Echeverria <jecheverria@bytes4run.com>
 * @package SIMA\HELPERS\ViewBuilder
 * @version 1.0.0
 * @date 2024-03-06
 * @time 11:00:00
 * @copyright (c) 2024 Bytes4Run
 */

declare (strict_types = 1);

namespace SIMA\HELPERS;

use SIMA\HELPERS\Configs;
use SIMA\LIBRARIES\ViewEngine;

class ViewBuilder
{
    /**
     * @var array
     */
    private array $vars;

    /**
     * @var string
     */
    private string $view;

    /**
     * @var string
     */
    private string $path;

    /**
     * @var string
     */
    private string $theme;

    /**
     * @var ViewEngine
     */
    private ViewEngine|string $engine;
    /**
     * @var string
     */
    private string $token;

    /**
     * Constructor
     */
    public function __construct()
    {
        $conf        = new Configs();
        $this->vars  = $conf->get('config', 'json');
        $this->theme = $this->vars['app_view']['engine'] . "/" . $this->vars['app_view']['theme'];
        if ($this->vars['app_view']['engine'] !== 'json') {
            $this->engine = new ViewEngine($this->vars['app_view']['engine']);
        } else {
            $this->engine = 'json';
        }
    }

    /**
     * Function to render the view
     * @param string|array $view
     * @param array|null $data
     * @return void
     */
    public function render(string | array $view, array $data = []): void
    {
        $this->token = $_SESSION['token'] ?? '';
        if ($this->engine !== "json") {
            if ($this->find($view)) {
                $this->engine->assign('data', $this->createData($data));
                $this->engine->assign('token', $this->token);
                $this->engine->assign('theme', $this->theme);
                $this->engine->render($this->path);
            } else {
                $this->buildDefaultView($view, $data, 'not_found');
            }
        } else {
            header('Content-Type: application/json'); //Especificamos el tipo de contenido a devolver
            $code = (isset($data['code'])) ? $data['code'] : $view['data']['code'];
            http_response_code(intval($code));
            echo json_encode($data, JSON_THROW_ON_ERROR); //Devolvemos el contenido
        }
    }

    /**
     * Function to find the view
     * @param string|array $view
     * @return bool
     */
    private function find(string | array $view): bool
    {
        if (is_array($view)) {
            if ($view['type'] !== "json") {
                $this->path = $this->getViewPath($view);
                return file_exists($this->path);
            }
            $view = ['type' => "template", 'name' => $view];
        }
        return false;
    }

    /**
     * Function to create structured data for view
     * @param array $data
     * @return array
     */
    private function createData(array $data): array
    {
        $userData = [];
        if (! empty($data['view'])) {
            $title = str_replace("/", " | ", $data['view']['name']);
        }
        if (! empty($data['user'])) {
            $userData = $data['user'];
        }
        $this->vars['technology'][0]['name'] = "PHP " . phpversion();
        $this->vars['technology'][0]['icon'] = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M5 12v-7a2 2 0 0 1 2 -2h7l5 5v4" /><path d="M5 18h1.5a1.5 1.5 0 0 0 0 -3h-1.5v6" /><path d="M17 18h1.5a1.5 1.5 0 0 0 0 -3h-1.5v6" /><path d="M11 21v-6" /><path d="M14 15v6" /><path d="M11 18h3"/></svg>';
        $cssClasses                          = $this->getGlobalBuildAsset('css');
        if ($cssClasses !== null) {
            $this->vars['app_css'] = $cssClasses;
        }
        $jsClasses = $this->getGlobalBuildAsset('js');
        if ($jsClasses !== null) {
            $this->vars['app_js'] = $jsClasses;
        }
        return [
            'content' => $data,
            'layout'  => [
                'head'    => [
                    'template' => "/components/_shared/Head.tpl",
                    'data'     => [
                        'page_title' => $title ?? "",
                        'meta'       => $this->getMeta(),
                        'css'        => [],
                        'js'         => [],
                    ],
                ],
                'footer'  => [
                    'template' => "/components/_shared/Footer.tpl",
                    'data'     => [],
                ],
                'navbar'  => [
                    'template' => "/components/_shared/Navbar.tpl",
                    'data'     => [
                        'app_logo' => (isset($userData['mode']) && $userData['mode'] == "dark") ? $this->vars['darkLogo'] : $this->vars['app_logo'],
                        'user'     => $userData,
                    ],
                ],
                'scripts' => '',
                'app'     => [
                    'data' => $this->vars,
                ],
            ],
        ];
    }

    /**
     * Function to get the view path
     * @param array $view
     * @return string
     */
    private function getViewPath(array $view): string
    {
        $path = _VIEW_ . $this->theme . "/";
        switch ($view['type']) {
            case "template":
                $name = explode('/', $view['name']);
                if (count($name) > 2) {
                    $app      = $name[0];
                    $module   = $name[1];
                    $viewName = $name[2];
                    $path .= $app . "/modules/" . $module . $viewName . ".tpl";
                } elseif (count($name) == 2) {
                    $path .= "modules/" . $view['name'] . ".tpl";
                } else {
                    $path .= "default/templates/" . $name[0] . ".tpl";
                }
                break;
            case "layout":
                $name = explode('/', $view['name']);
                if (count($name) > 2) {
                    $app      = $name[0];
                    $module   = $name[1];
                    $viewName = $name[2];
                    $path .= $app . "/" . $module . "/layouts/" . $viewName . ".tpl";
                } elseif (count($name) == 2) {
                    $module   = $name[0];
                    $viewName = $name[1];
                    $path .= $module . "/layouts/" . $viewName . ".tpl";
                } else {
                    $path .= "default/layouts/" . $name[0] . ".tpl";
                }
                break;
            default: $view['name'] . ".tpl";
                break;
        }
        return $path;
    }

    /**
     * Función que devuelve la lista de metadata para la vista
     * @return array
     */
    private function getMeta(): array
    {
        return [
            ['meta_name' => "msapplication-TileColor", 'meta_content' => $this->vars['app_title_color']],
            ['meta_name' => "msapplication-TileImage", 'meta_content' => "assets/img/app_icons/ms-icon-144x144.png"],
            ['meta_name' => "theme-color", 'meta_content' => $this->vars['app_theme_color']],
            ['meta_name' => "background_color", 'meta_content' => $this->vars['app_background_color']],
            ['meta_name' => "apple-mobile-web-app-capable", 'meta_content' => "yes"],
            ['meta_name' => "apple-mobile-web-app-status-bar-style", 'meta_content' => "black"],
            // ['meta_name' => "apple-mobile-web-app-title", 'meta_content' => $this->vars['app_name']],
            // ['meta_name' => "application-name", 'meta_content' => $this->vars['app_name']],
            // ['meta_name' => "description", 'meta_content' => $this->vars['app_description']],
            ['meta_name' => "format-detection", 'meta_content' => "telephone=no"],
            ['meta_name' => "mobile-web-app-capable", 'meta_content' => "yes"],
            ['meta_name' => "msapplication-config", 'meta_content' => ""],
            ['meta_name' => "msapplication-tap-highlight", 'meta_content' => "no"],
            ['meta_name' => "viewport", 'meta_content' => "width=device-width, initial-scale=1, shrink-to-fit=no"],
        ];
    }

    /**
     * Function to build the default view
     * @param string|array $view
     * @param array $data
     * @param string $type
     * @return void
     */
    private function buildDefaultView(string | array $view, array $data, string $type): void
    {
        $this->path = _VIEW_ . $this->theme . "/default/" . $type . ".tpl";
        $viewData   = [
            'content' => $data,
            'view'    => $view,
            'layout'  => [
                'head'    => [
                    'template'   => "_shared/templates/_head.tpl",
                    'css'        => '',
                    'page_title' => $type,
                    'meta'       => $this->getMeta(),
                ],
                'body'    => ['layout' => 'hold-transition sidebar-mini layout-fixed', 'darkmode' => false],
                'footer'  => [
                    'template' => "_shared/templates/_footer.tpl",
                    'data'     => [],
                ],
                'scripts' => '',
                'app'     => [
                    'data' => $this->vars,
                ],
            ],
        ];
        $this->engine->assign('data', $viewData);
        $this->engine->assign('token', $this->token);
        $this->engine->assign('theme', $this->theme);
        $this->engine->render($this->path);
    }

    /**
     * Get the full filename of the build asset that starts with "global-"
     * Looks into the public/build/assets directory defined by _ASSETS_.
     *
     * @param string|null $type Optional filter: 'css' or 'js' to prefer extensions
     * @return string|null Returns the filename (relative to public/) or null if not found
     */
    private function getGlobalBuildAsset(?string $type = null): ?string
    {
        // Build the directory path where Vite places built assets
        $assetsDir = rtrim(_ASSETS_, "\\/") . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR;

        if (! is_dir($assetsDir)) {
            return null;
        }

        $files = scandir($assetsDir);
        if ($files === false) {
            return null;
        }

        $preferredExt = null;
        if ($type === 'css') {
            $preferredExt = '.css';
        } elseif ($type === 'js') {
            $preferredExt = '.js';
        }

        // First try to find a file that matches the preferred extension
        foreach ($files as $f) {
            if ($f === '.' || $f === '..') {
                continue;
            }

            if (str_starts_with($f, 'global-')) {
                if ($preferredExt !== null && str_ends_with($f, $preferredExt)) {
                    return 'build/assets/' . $f;
                }
            }
        }

        // Fallback: return the first file that starts with global-
        foreach ($files as $f) {
            if ($f === '.' || $f === '..') {
                continue;
            }

            if (str_starts_with($f, 'global-')) {
                return 'build/assets/' . $f;
            }
        }

        return null;
    }
}
