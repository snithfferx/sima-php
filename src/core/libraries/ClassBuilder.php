<?php
/** 
 * ClassBuilder
 * @description Helper to build classes
 * @author Jorge Echeverria <jecheverria@bytes4run.com> 
 * @category Library
 * @package SIMA\LIBRARIES\ClassBuilder
 * @version 1.0.0 rev.1 
 * @date 2024-04-16
 * @time 16:37:00
 * @copyright (c) 2023 Bytes4Run 
 */
declare(strict_types=1);
namespace SIMA\LIBRARIES;

use SIMA\HELPERS\Definer;
use SIMA\HELPERS\Configs;

class ClassBuilder
{
	private array | null $app_env;
    public function __construct()
    {
        new Definer;
		$configs = new Configs;
		$this->app_env = $configs->get('config','json');
    }

    /**
     * @param array $values [type] [name] [options] [location]
     * @return bool|string
	 * @example php build class MyClass -a // This will create a module with its models, controllers, helpers, handlers and view
	 * @example php build class MyClass -f modules // This will create a module with its models, controllers, helpers, handlers and view in the modules directory
     */
    public function build(array $values): bool|string
    {
        return match ($values[1]) {
            'model' => $this->buildModel($values[2], $values[3] ?? null, $values[4] ?? null),
            'controller' => $this->buildController($values[2], $values[3] ?? null, $values[4] ?? null),
            'class' => $this->buildClass($values[2], $values[3] ?? null, $values[4] ?? null),
            'view' => $this->buildView($values[2], $values[3] ?? null, $values[4] ?? null),
            'helper' => $this->buildHelper($values[2], $values[3] ?? null, $values[4] ?? null),
            'handler' => $this->buildHandler($values[2], $values[3] ?? null, $values[4] ?? null),
            default => "The type $values[1] is not valid\n",
        };
    }
    /**
     * Prints a help message for the class builder CLI mode.
     *
     * @return string
     */
    public function help(): string
    {
        return "This is a class builder for CLI mode. Here's how to use it:\n
           To create a new class use: 'php build [type_of_class] [class_name] [options] [location]'\n
           Replace 'type_of_class' with the type of class you want to create (e.g., 'model', 'controller', 'modelcontroller').\n
           Replace 'class_name' with the name of the class.\n
           Replace 'options' with any additional options you want to include in the class.\n
           Replace 'location' with the location where you want to save the class.\n
           For example, to create a new model class named 'User' in the 'models' directory, use: 'php model User models'\n
           To see this help message again, use: 'php help'\n
           ['options']:\n
               -f --force: Force the creation of the class even if it already exists\n
               -h --help: Show this help message\n
               -c --components: Create a class with components.\n
			   -m --model: Create a module's model.\n
			   -v --view: Create a module's view.\n
			   -ctr --controller: Create a module's controller.\n
			   -cm --controller-model: Create a module's controller and model.\n
			   -hp --helper: Create a module's helper.\n
			   -a --all: Create a module with all components.\n
			   -hd --handler: Create a module's handler.\n";
			   
    }
    /**
     * Builds a model class based on the given name, options, and location.
     *
     * @param string $name The name of the model
     * @param string|null $options The options for building the model (default: null)
     * @param string|null $location The location of the model (default: null)
     * @return bool|string Returns a message indicating whether the model was created or already exists, or null if an error occurred
     */
    private function buildModel(string $name, string|null $options, string|null $location): bool|string
    {
        $nameUC = ucfirst($name);
        $model_base = $this->getBase($name, 'Model');
        $content = '';
        if ($options == '-c' || $options == '--components') {
            $model_template = file_get_contents(__DIR__ . "/_build/templates/_model_components.tp");
            $content = str_replace('{name}', $nameUC, $model_template);
        } else {
            $content = "    private array | null \$error;\nprivate array | null \$response;";
            $content .= "    public function __construct() {\n";
            $content .= "        parent::__construct();\n";
            $content .= "    }\n";
        }
        $content = str_replace('{component_content}', $content, $model_base['base']);
        $content = str_replace('{component_model}', 'use SIMA\\ENTITIES\\' . $nameUC . ';', $content);
        $filePath = (!is_null($location)) ? _MODULE_ . "$location/models/": _MODULE_ . "$name/models/";
        $fileName = $nameUC . "Model.php";
        $force = $options == '-f' || $options == '--force';

        return $this->createFile($filePath, $fileName, $content, $force);
    }

    private function buildController(string $name, string|null $options = null, string|null $location = null): string
    {
        $nameUC = ucfirst($name);
        $controller_basic = $this->getBase($name, 'Controller');
        $content = "";
        if ($options == '-c' || $options == '--components') {
            $controller_components = file_get_contents(__DIR__ . "/_build/templates/_controller_components.tp");
            $content = str_replace('{name}', $nameUC, $controller_components);
        } else {
            // Constructor
            $content = "    private " . $nameUC . "Model \$model;\n";
            $content .= "    public function __construct(int \$id = null) {\n";
            $content .= "        \$this->model = new " . $nameUC . "Model;\n";
            $content .= "    }\n";
            // CRUD
            $content .= "    public function index() {\n";
            $content .= "        \$this->model->getAll();\n";
            $content .= "    }\n";
            $content .= "    public function show(int \$id) {\n";
            $content .= "        \$this->model->get(\$id);\n";
            $content .= "    }\n";
        }
        $content = str_replace('{component_content}', $content, $controller_basic['base']);
        $content = str_replace('{component_model}', 'use SIMA\\MODULES\\' . $nameUC . '\\models\\' . $nameUC . 'Model;', $content);
        $filePath = (!is_null($location)) ? _MODULE_ . "$location/Controllers/" : _MODULE_ . "$name/Controllers/";
        $fileName = $name . "Controller.php";
        $force = $options == '-f' || $options == '--force';

        return $this->createFile($filePath, $fileName, $content, $force);
    }
	/**
	 * Create a class file module depending on options
	 * 
	 * @param string $name
	 * @param string|null $options
	 * @param string|null $location
	 * @return string
	 */
    private function buildClass(string $name, string|null $options = null, string|null $location = null): string
    {
        return match ($options) {
            '-ctr', '--controller' => $this->buildController($name, $options, $location),
            '-cm' => $this->buildModelController($name, $options, $location),
            '-c' => $this->buildClass($name, $options, $location),
            '-v', '--view' => $this->buildView($name, $options, $location),
            '-hp', '--helper' => $this->buildHelper($name, $options, $location),
            '-hd', '--handler' => $this->buildHandler($name, $options, $location),
            '-m', '--model' => $this->buildModel($name, $options, $location),
            '-a', '--all' => $this->buildAll($name, $location),
            default => "Method not supported: $options",
        };
    }

    /**
     * @param string $name
     * @param string|null $type
     * @return string
     */
    public function getBase(string $name, string $type): array
    {
		$nameUC = ucfirst($name);
		$typeUC = ucfirst($type);
		$className = $nameUC.$typeUC;
		$fields = [
				'{component_name}',
				'{component_name_lower}',
				'{component_classname}',
				'{author_name}',
				'{author_email}',
				'{component_type}',
				'{component_type_lower}',
				'{component_type_class}',
				'{component_date}',
				'{component_time}',
				'{component_year}',
				'{author_company}'
		];
		$values = [
			$nameUC,
			strtolower($name),
			$className,
			$this->app_env['author_name'],
			$this->app_env['author_email'],
			strtoupper($type),
			strtolower($type),
			ucfirst(strtolower($type)),
			date('Y-m-d'),
			date('H:i:s'),
			date('Y'),
			$this->app_env['author_company']
		];
        $_basic = file_get_contents(__DIR__ . "/_build/templates/_base_class.tp");
        $_basic = str_replace($fields, $values, $_basic);
        return [
			'base' => $_basic,
			'component' => $values
		];
    }
    
    /**
     * Builds both model and controller classes based on the given name, options, and location.
     *
     * @param string $name The name of the component
     * @param string|null $options The options for building the components (default: null)
     * @param string|null $location The location of the components (default: null)
     * @return string Returns a message indicating whether the components were created or already exist
     */
    private function buildModelController(string $name, string|null $options = null, string|null $location = null): string
    {
        $modelResult = $this->buildModel($name, $options, $location);
        $controllerResult = $this->buildController($name, $options, $location);
        
        $nameUC = ucfirst($name);
        return "Model and Controller components for $nameUC have been processed:\n$modelResult$controllerResult";
    }

    private function buildView(string $name, string|null $options = null, string|null $location = null): string
    {
        $nameUC = ucfirst($name);
        $moduleDir = $location ?? $name;
        $engine = $this->app_env['app_view']['engine'];
        $theme = $this->app_env['app_view']['theme'];
        $filePath = _VIEW_ . "views/$engine/$theme/$moduleDir/";
        $fileName = $name . ".tpl";
        $content = "{{* Smarty template for module $nameUC *}}\n";
        $force = $options == '-f' || $options == '--force';

        return $this->createFile($filePath, $fileName, $content, $force);
    }

    private function buildHelper(string $name, string|null $options = null, string|null $location = null): string
    {
        $nameUC = ucfirst($name);
        $moduleDir = $location ?? $name;

        $helper_base = $this->getBase($name, 'Helper');

        $content = '';
        if ($options == '-c' || $options == '--components') {
            $helper_template = "    // Component-based helper content for {name}\n";
            $content = str_replace('{name}', $nameUC, $helper_template);
        } else {
            $content = "    public function __construct() {\n";
            $content .= "    }\n";
        }

        $finalContent = str_replace('{component_content}', $content, $helper_base['base']);
        $finalContent = str_replace('{component_model}', '', $finalContent);

        $targetNamespace = 'namespace SIMA\\MODULES\\' . ucfirst($moduleDir) . '\\helpers;';
        $finalContent = preg_replace('/namespace\\s+SIMA\\\\[^;]+;/', $targetNamespace, $finalContent);

        $filePath = _MODULE_ . "$moduleDir/helpers/";
        $fileName = $nameUC . "Helper.php";
        $force = $options == '-f' || $options == '--force';

        return $this->createFile($filePath, $fileName, $finalContent, $force);
    }

    private function buildHandler(string $name, string|null $options = null, string|null $location = null): string
    {
        $nameUC = ucfirst($name);
        $moduleDir = $location ?? $name;

        $handler_base = $this->getBase($name, 'Handler');

        $content = '';
        if ($options == '-c' || $options == '--components') {
            $handler_template = "    // Component-based handler content for {name}\n";
            $content = str_replace('{name}', $nameUC, $handler_template);
        } else {
            $content = "    public function __construct() {\n";
            $content .= "    }\n";
        }

        $finalContent = str_replace('{component_content}', $content, $handler_base['base']);
        $finalContent = str_replace('{component_model}', '', $finalContent);

        $targetNamespace = 'namespace SIMA\\MODULES\\' . ucfirst($moduleDir) . '\\handlers;';
        $finalContent = preg_replace('/namespace\\s+SIMA\\\\[^;]+;/', $targetNamespace, $finalContent);

        $filePath = _MODULE_ . "$moduleDir/handlers/";
        $fileName = $nameUC . "Handler.php";
        $force = $options == '-f' || $options == '--force';

        return $this->createFile($filePath, $fileName, $finalContent, $force);
    }

    private function buildAll(string $name, string|null $options = null, string|null $location = null): string
    {
        $results = [];
        $results[] = $this->buildModel($name, $options, $location);
        $results[] = $this->buildController($name, $options, $location);
        $results[] = $this->buildView($name, $options, $location);
        $results[] = $this->buildHelper($name, $options, $location);
        $results[] = $this->buildHandler($name, $options, $location);
        
        $nameUC = ucfirst($name);
        $output = "All components for module $nameUC have been processed:\n";
        foreach ($results as $result) {
            $output .= "- $result";
        }
        return $output;
    }

    private function createFile(string $filePath, string $fileName, string $content, bool $force = false): string
    {
        if (file_exists($filePath . $fileName) && !$force) {
            return "The file $fileName already exists\n";
        }

        if (!file_exists($filePath)) {
            mkdir($filePath, 0755, true);
        }

        file_put_contents($filePath . $fileName, $content);

        return "The file $fileName has been created\n";
    }
}
