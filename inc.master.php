<?php

// Determine our absolute document root.
define('DOC_ROOT', realpath(dirname(__FILE__) . '/../'));

// Register autoload and include miscellaneous global functions.
require DOC_ROOT . '/framework/inc.functions.php';

// Load config settings.
Config::initialize();

// Strip out www prefix.
if (Config::get('strip_www') &&
    substr(@$_SERVER['SERVER_NAME'], 0, 4) == 'www.') {
   $s = @$_SERVER['HTTPS'] ? 's' : '';
   $domain = substr($_SERVER['SERVER_NAME'], 4);
   Page::redirect("http{$s}://{$domain}{$_SERVER['REQUEST_URI']}");
}

// If not on a CLI, start the session.
if (php_sapi_name() != 'cli') {
   // Store session info in the database?
   if (Config::get('dbsessions'))
     DBSession::register();

   // Initialize the session.
   session_name('dmphps');
   session_start();
}

