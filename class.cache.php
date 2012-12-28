<?php
/**
 * APC cache with memcached fallback.
 */

Cache::init();

class Cache {
   private static $cache = null;

   public static function init() {
      if (!self::$cache) {
         if (class_exists('Memcached')) {
            self::$cache = new Memcached();
            self::$cache->addServer('localhost', 11211);
         }
         else if (APCCache::enabled()) {
            self::$cache = new APCCache();
         }
         else {
            self::$cache = new DummyCache();
         }
      }
   }

   public static function registerForSessions() {
     if (!class_exists('Memcached'))
       return false;

     ini_set('session.save_handler', 'memcached');
     ini_set('session.save_path', 'localhost:11211');

     return true;
   }

   public static function get($key) {
      $key = @Config::get('cache.prefix') . $key;
      return self::$cache->get($key);
   }

   public static function set($key, $value, $expires = 0) {
      $key = @Config::get('cache.prefix') . $key;
      return self::$cache->set($key, $value, $expires);
   }

   public static function delete($key) {
      $key = @Config::get('cache.prefix') . $key;
      return self::$cache->delete($key);
   }

   public static function increment($key, $offset = 1) {
      $key = @Config::get('cache.prefix') . $key;
      return self::$cache->increment($key, $offset);
   }

   public static function decrement($key, $offset = 1) {
      $key = @Config::get('cache.prefix') . $key;
      return self::$cache->decrement($key, $offset);
   }
}

class APCCache {
   public static function enabled() {
      return function_exists('apc_fetch');
   }

   public function get($key) {
      return apc_fetch($key);
   }

   public function set($key, $value, $expires = 0) {
      if ($expires > time()) {
         $expires -= $time;
      }
      return apc_store($key, $value, $expires);
   }

   public function delete($key) {
      return apc_delete($key);
   }

   public function increment($key, $offset = 1) {
      return apc_inc($key, $offset);
   }

   public function decrement($key, $offset = 1) {
      return apc_dec($key, $offset);
   }
}

class DummyCache {
   public function get($key) {
      return false;
   }

   public function set($key, $value, $expires = 0) {
      return false;
   }

   public function delete($key) {
      return false;
   }

   public function increment($key, $offset = 1) {
      return false;
   }

   public function decrement($key, $offset = 1) {
      return false;
   }
}

