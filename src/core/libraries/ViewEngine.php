<?php
/**
 * ViewEngine Library
 * @description This class is used to initialize and configure the engine to render the views
 * @category Library
 * @author Jorge Echeverria <jecheverria@bytes4run.com>
 * @package SIMA\LIBRARIES\ViewEngine
 * @version 1.0.0
 * @date 2024-03-06 - 2025-04-03
 * @time 11:00:00
 * @copyright (c) 2022-2025 Bytes4Run
 */

declare (strict_types = 1);

namespace SIMA\LIBRARIES;

use Smarty\Smarty;
// use SIMA\TYPES\Response;

class ViewEngine
{
    /**
     * @var mixed
     */
    private mixed $engineClass;
    /**
     * Constructor
     */
    public function __construct(
        private string $engine = 'smarty',
        private array $literal = ['left' => '{{', 'right' => '}}'],
        private string $viewPath = _VIEW_,
        private bool $caching = false) {
        $this->initEngine();
    }

    /**
     * Function to assign a variable to the view
     * @param string $name
     * @param mixed $value
     * @return void|\Exception
     */
    public function assign(string $name, $value): void {
        switch ($this->engine) {
            case 'smarty':
                $this->engineClass->assign($name, $value);
                break;
            case 'twig':
                $this->engineClass->addGlobal($name, $value);
                break;
            default:
                throw new \Exception("Engine not supported");
                break;
        }
    }

    /**
     * Function to render the view
     * @param string $view
     * @return void|\Exception
     */
    public function render(string $view): void {
        $renderized = '';
        switch ($this->engine) {
            case 'smarty':
                $renderized = $this->engineClass->fetch($view);
                break;
            case 'twig':
                $renderized = $this->engineClass->render($view);
                break;
            default:
            $renderized = json_encode(['message'=>"Engine not supported", 'status'=>500]);
                break;
        }
        echo $renderized;
        /* echo "<pre>";
        var_dump($this->engine);
        echo "</pre>";
        exit; */
    }

    /**
     * Function to initialize the engine
     *
     * @param string $engine
     * @param array $literal
     * @param bool $caching
     * @return void|\Exception
     */
    private function initEngine(): void
    {
        switch ($this->engine) {
            case 'smarty':
                $this->initSmarty($this->literal, $this->caching);
                break;
            case 'twig':
                $this->initTwig($this->viewPath, _CACHE_ . "twig/cache/");
                break;
            case 'json':
                break;
            default:
                throw new \Exception("Engine not supported");
                break;
        }
    }

    /**
     * Function to initialize the Smarty engine
     *
     * @param array $literal
     * @param bool $caching
     * @return void
     */
    private function initSmarty(array $literal, bool $caching): void
    {
        $this->engineClass = new Smarty();
        $this->engineClass->setTemplateDir(_VIEW_);
        $this->engineClass->setConfigDir(_CONF_ . "smarty/config");
        $this->engineClass->setCacheDir(_CACHE_ . "smarty/cache/");
        $this->engineClass->setCompileDir(_CACHE_ . "smarty/compiles/");
        $this->engineClass->setLeftDelimiter($literal['left']);
        $this->engineClass->setRightDelimiter($literal['right']);
        $this->engineClass->caching = $caching;
    }

    /**
     * Function to initialize the Twig engine
     *
     * @param array $literal
     * @param bool $caching
     * @return void
     */
    private function initTwig(string $viewPath, string $cachePath): void
    {
        $loader = new \Twig\Loader\FilesystemLoader($viewPath);
        $this->engineClass = new \Twig\Environment($loader, [
            'cache' => $cachePath,
            'auto_reload' => true,
        ]);
    }

    public function json(Array $data) {
        // get headers
        header('Content-Type: application/json'); //Especificamos el tipo de contenido a devolver
        http_response_code(intval($data['status_code']));
        echo json_encode($data, JSON_THROW_ON_ERROR); //Devolvemos el contenido
    }
}
