<?php
/**
 * Entry point for all requests.
 */

// If running with php's built in web server, check for static assets.
if (php_sapi_name() == 'cli-server' &&
    ($uri = $_SERVER['REQUEST_URI']) != '/') {
   $root = $_SERVER['DOCUMENT_ROOT'];
   if (file_exists("{$root}{$uri}")) {
      return false;
   }
}

// Load up the framework.
require '../framework/inc.master.php';

// Route the request.
$router = new Router();
$router->go();

