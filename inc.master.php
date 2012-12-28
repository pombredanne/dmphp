<?php

// Determine our absolute document root.
define('DOC_ROOT', realpath(dirname(__FILE__) . '/../'));

// Register autoload and include miscellaneous global functions.
require DOC_ROOT . '/framework/inc.functions.php';

// Load config settings.
Config::initialize();

// Redirect to canonical host.
$host = @$_SERVER['SERVER_NAME'];
$canonical = @Config::get('host.canonical');
if ($canonical && $host && $host != $canonical) {
   $s = @$_SERVER['HTTPS'] ? 's' : '';
   Page::redirect("http{$s}://{$canonical}{$_SERVER['REQUEST_URI']}");
}

// If not on a CLI, start the session.
if (php_sapi_name() != 'cli') {
   if (($sessions = Config::get('sessions'))) {
      switch ($sessions) {
      case 'memcached': Cache::registerForSessions(); break;
      case 'db': DBSession::register(); break;
      }

      // Initialize the session.
      session_name('dmphps');
      session_start();
   }
}

