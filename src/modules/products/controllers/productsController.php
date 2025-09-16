<?php
/** 
 * Products
 * @description ProductsController class for manage Products
 * @author Bytes4Run <info@bytes4run.com>
 * @category CONTROLLER
 * @package SIMA\MODULES\Products\controllers\ProductsController
 * @version 1.0.0
 * @date 2025-09-16
 * @time 07:40:07
 * @copyright (c) 2025 Bytes4Run
 */
# Strict types
declare(strict_types=1);
# Namespace
namespace SIMA\MODULES\products\controllers;
# Base
use SIMA\CLASSES\Controller;
use Throwable;
# Classes
use SIMA\MODULES\products\models\ProductModel;
class ProductsController extends Controller {
        private ProductModel $model;
    public function __construct(int $id = null) {
        $this->model = new ProductModel;
    }
}
?>
