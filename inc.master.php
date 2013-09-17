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
$subdomains = @Config::get('host.subdomains');
if ($canonical && $host && $host != $canonical && !in_array($host, $subdomains)) {
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
   } else {
      // Mimic session_cache_limiter('nocache')
      // http://us2.php.net/session_cache_limiter
      header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
      header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
      header('Pragma: no-cache');
   }
}

