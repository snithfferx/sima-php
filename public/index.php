<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
/**
 * This is the main file that will be loaded when the user visits the website, and lunch the application
 */
require __DIR__ . '/../vendor/autoload.php';

# NameSpace For main root loader
use SIMA\bootstrap\Loader;

# Instance of App to Initialize the application and apply authorization and cors resolution
$app = new Loader();
# Obtaining Module response
$app->run();
$response = $app->getResponse();
if ($response === null) {
    $response = $app->getError();
}
# Rendering the view or message
$app->render($response);
# Destroying application instance
$app->end();
