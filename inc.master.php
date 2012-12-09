<?php

// Determine our absolute document root.
define('DOC_ROOT', realpath(dirname(__FILE__) . '/../'));

// Register autoload and include miscellaneous global functions.
require DOC_ROOT . '/framework/inc.functions.php';

// Load config settings.
Config::initialize();

if (php_sapi_name() != 'cli') {
   // Store session info in the database?
   if (Config::get('dbsessions'))
     DBSession::register();

   // Initialize the session.
   session_name('dmphps');
   session_start();
}

