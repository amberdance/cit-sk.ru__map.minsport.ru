<?php

use Citsk\Controllers\Router;

require $_SERVER['DOCUMENT_ROOT'] . "/api/vendor/autoload.php";

setEnvironmentMode('dev');

$API = new Router;
$API->setHTTPHeaders()
    ->initializeParameters()
    ->initializeRouting();
