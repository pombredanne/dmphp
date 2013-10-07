<?php
/**
 * Parses and serves configuration from `config.ini`.
 */

define('CONFIG_FILE', '../config.ini');

class Config {
   public $ini;
   private static $me;

   public static function initialize() {
      return static::getConfig();
   }

   // Singleton
   public static function getConfig() {
      if (is_null(self::$me)) {
         self::$me = new Config();
      }
      return self::$me;
   }

   private function __construct() {
      if (!file_exists(CONFIG_FILE)) {
         trigger_error("Missing required file: config.ini", E_USER_ERROR);
      }

      $this->ini = parse_ini_file(CONFIG_FILE, true);

      // Overrides for dev/staging/prod environments.
      if (($server = $this->whichServer())) {
         foreach ($this->ini[$server] as $key => $value) {
            $this->ini[$key] = $value;
         }
      }

      // Pass along "ini_set." values.
      foreach ($this->ini as $key => $value) {
         if (preg_match('/^ini_set\.(.*?)$/', $key, $match)) {
            ini_set($match[1], $value);
         }
      }

      // Setup paths relative to DOC_ROOT.
      $this->ini['page.templates_dir'] = DOC_ROOT . '/templates';
   }

   public function whichServer() {
      $dev   = (array)$this->ini['hosts.development'];
      $stage = (array)$this->ini['hosts.staging'];
      $prod  = (array)$this->ini['hosts.production'];

      // Attempt to use parse_url(), but fallback to HTTP_HOST if it fails.
      $info = parse_url(@$_SERVER['HTTP_HOST']);
      $http_host = @$info['host'] ?: @$_SERVER['HTTP_HOST'];

      foreach ($prod as $regex) {
         if (preg_match($regex, $http_host)) {
            return 'production';
         }
      }

      foreach ($stage as $regex) {
         if (preg_match($regex, $http_host)) {
            return 'staging';
         }
      }

      foreach ($dev as $regex) {
         if (preg_match($regex, $http_host)) {
            return 'development';
         }
      }

      return false;
   }

   public static function get($key) {
      return @self::getConfig()->ini[$key];
   }
}

