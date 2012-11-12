<?php
/**
 * Generates, validates, and stores tokens to prevent CSRF attacks.
 */

class CSRF {
   private static $key = 'csrf_tokens';
   private static $storage = array(50, 100);

   public static function generate() {
      $token = sha1(rand());
      $_SESSION[static::$key][] = $token;

      // When we reach static::$storage[1] keys, prune to static::$storage[0].
      if (count($_SESSION[static::$key]) > static::$storage[1]) {
         $_SESSION[static::$key] =
            array_slice($_SESSION[static::$key], static::$storage[0]);
      }

      return $token;
   }

   public static function validate($token) {
      if ($token && count(@$_SESSION[static::$key]) > 0) {
         foreach ($_SESSION[static::$key] as $valid) {
            if ($token == $valid) {
               return true;
            }
         }
      }
      return false;
   }
}

