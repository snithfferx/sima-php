<?php
/** 
 * Products
 * @description ProductsModel class for manage Products
 * @author Bytes4Run <info@bytes4run.com>
 * @category MODEL
 * @package SIMA\MODULES\products\models\ProductModel
 * @version 1.0.0
 * @date 2025-09-16
 * @time 07:40:07
 * @copyright (c) 2025 Bytes4Run
 */
# Strict types
declare(strict_types=1);
# Namespace
namespace SIMA\MODULES\products\models;
# Base
use SIMA\CLASSES\Model;
class ProductModel extends Model {
    private array | null $error;
    private array | null $response;
    public function __construct() {
        parent::__construct();
    }

}
?>
