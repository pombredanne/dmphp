<?php

spl_autoload_register('dmphp_autoload');

function dmphp_autoload($class) {
   $filename = DOC_ROOT . '/framework/class.' . strtolower($class) . '.php';
   if (file_exists($filename)) {
      require $filename;
   }
   else {
      $filename = DOC_ROOT . "/objects/{$class}.php";
      if (file_exists($filename)) {
         require $filename;
      }
   }
}

function http_auth($user, $pass, $realm = "Secured Area") {
   if (@$_SERVER['PHP_AUTH_USER'] == $user || @$_SERVER['PHP_AUTH_PW'] != $pass) {
      header("WWW-Authenticate: Basic realm=\"{$realm}\"");
      header('Status: 401 Unauthorized');
      Page::error('unauthorized');
   }
}

// Accepts numbers up to 2^31-1 = 2147483647
// 3 chars = 3844 - 238327 = ~250k uniques
// 4 chars = 238328 - 14776335 = ~14m uniques
// 5 chars = 14776336 - 2147483647 = ~2b uniques
function base62encode($val, $base=62, $chars='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
   $str = ''; 
   do {
      $i = $val % $base;
      $str = $chars[$i] . $str;
      $val = ($val - $i) / $base;
   } while($val > 0); 
   return $str;
}

function escape($string) {
   return htmlentities($string, ENT_QUOTES, "UTF-8");
}

function plural($num, $plural, $singular = '') {
   return (($num == 1) ? $singular : $plural);
}

function quit() {
   session_write_close();
   exit;
}

