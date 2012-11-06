<?php
/**
 * Builds and displays templates. Automatically swaps in minified resources if 
 * available.
 */

class Page {
   private static $title;
   private static $scripts = array();
   private static $stylesheets = array();

   public static function setTitle($title) {
      self::$title = $title;
   }

   public static function getTitle($includeSuffix = true) {
      if (isset(self::$title)) {
         $suffix = ($includeSuffix ? Config::get('page.title_suffix') : '');
         return self::$title . $suffix;
      }
      return Config::get('page.default_title');
   }

   public static function addScripts() {
      // Accept either an argument list or an array.
      $args = func_get_args();
      if (count($args) == 1 && is_array($args[0])) {
         $args = array_pop($args);
      }

      // Filter out blanks.
      $args = array_filter($args);

      // Merge with existing scripts.
      self::$scripts = array_merge(self::$scripts, $args);
   }

   public static function getScripts() {
      return self::$scripts;
   }

   public static function addStylesheets() {
      // Accept either an argument list or an array.
      $args = func_get_args();
      if (count($args) == 1 && is_array($args[0])) {
         $args = array_pop($args);
      }

      // Filter out blanks.
      $args = array_filter($args);

      // Merge with existing stylesheets.
      self::$stylesheets = array_merge(self::$stylesheets, $args);
   }

   public static function getStylesheets() {
      return self::$stylesheets;
   }

   private static function applyOptions() {
      // Apply the resource prefixes.
      $path = '/scripts';
      foreach (self::$scripts as &$script) {
         if (!preg_match('#^(https?:)?//#', $script)) {
            $script = "{$path}/{$script}";
         }
      }

      $path = '/styles';
      foreach (self::$stylesheets as &$stylesheet) {
         if (!preg_match('#^(https?:)?//#', $stylesheet)) {
            $stylesheet = "{$path}/{$stylesheet}";
         }
      }

      // Minify JS.
      if (Config::get('minify.js')) {
         self::$scripts = Minifier::minifiedPaths(self::$scripts);
      }

      // Minify CSS.
      if (Config::get('minify.css')) {
         self::$stylesheets = Minifier::minifiedPaths(self::$stylesheets);
      }
   }

   public static function json($data) {
      echo json_encode($data);
      quit();
   }

   public static function display($template, $vars = array(), $includeLayout = true) {
      self::render($template, $vars, $includeLayout);
      quit();
   }

   public static function fetch($template, $vars, $includeLayout = true) {
      ob_start();
      self::render($template, $vars, $includeLayout);
      $result = ob_get_contents();
      ob_end_clean();
      return $result;
   }

   public static function render($template, $vars, $includeLayout = true) {
      self::applyOptions();

      extract($vars, EXTR_SKIP);

      $template = Config::get('page.templates_dir') . "/{$template}";

      require !$includeLayout ? $template :
         Config::get('page.templates_dir') . "/layout.tpl.php";
   }

   public static function error($heading = null, $message = null) {
      switch ($heading) {
         case 'not_found':
            header("HTTP/1.1 404 Not Found");
            Page::setTitle("Error 404 Not Found");
            $heading = "Page not found";
            $message = "The requested URL <em>{$_SERVER['REQUEST_URI']}</em> was not found.";
            break;
         case 'unauthorized':
            header("HTTP/1.1 401 Unauthorized");
            Page::setTitle("Error 401 Authorization Required");
            $heading = "Authorization Required";
            $message = "Invalid credentials.";
            break;
         case 'forbidden':
            header("HTTP/1.1 403 Forbidden");
            Page::setTitle("Error 403 Forbidden");
            $heading = "Forbidden";
            $message = "You are not authorized to access this page.";
            break;
         default:
            Page::setTitle($heading ?: "Error");
            $heading = $heading ?: "Unknown error";
            $message = $message ?: "An error occured and we couldn't process your request.";
            break;
      }

      Page::display('error.tpl.php', array(
         'heading' => $heading,
         'message' => $message
      ));

      quit();
   }

   public static function redirect($url, $code = 302) {
      switch ($code) {
         case 301: header("HTTP/1.1 301 Moved Permanently");  break;
         case 302: header("HTTP/1.1 302 Found");              break;
         case 303: header("HTTP/1.1 303 See Other");          break;
         case 304: header("HTTP/1.1 304 Not Modified");       break;
         case 305: header("HTTP/1.1 305 Use Proxy");          break;
         case 306: header("HTTP/1.1 306 Not Used");           break;
         case 307: header("HTTP/1.1 307 Temporary Redirect"); break;
      } 

      header("Location: {$url}");
      quit();
   }
}

// Add default JS + CSS sources.
Page::addScripts(Config::get('page.js'));
Page::addStylesheets(Config::get('page.css'));

