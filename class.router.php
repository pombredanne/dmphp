<?php
/**
 * Routes incoming requests to the appropriate handler.
 */

class Router {
   public static $cache_expires = 0;

   // Core members
   private $handler = 'index.php';
   private $arguments = array();

   // Convenience accessors
   private $method = null;
   private $post = array();
   private $get = array();
   private $session = array();

   /**
    * Inspects the request and populates member variables `handler` and 
    * `arguments` with the appropriate data.
    */
   public function parse() {
      // Strip query string from request path.
      $uri = preg_replace('/\?.*?$/', '', $_SERVER['REQUEST_URI']);

      // Add a directory to the top-level path for subdomains.
      $host = @$_SERVER['SERVER_NAME'];
      $subdomains = @Config::get('host.subdomains');
      if (in_array($host, $subdomains)) {
         $uri = '/' . substr($host, 0, strpos($host, '.')) . $uri;
      }

      // First check the cache.
      $key = "Router-{$uri}";
      if (($value = Cache::get($key))) {
         list($this->handler, $this->arguments) = $value;
      }
      // If not already cached, find the handler by traversing up the path.
      else {
         // Dynamically determine which handler to run.
         $pieces = explode('/', substr($uri, 1));

         do {
            // Default to index.php if nothing matched.
            if (!($path = implode('/', $pieces))) {
               $handler = DOC_ROOT . "/handlers/{$this->handler}";
            }
            else {
               // Check for path/index.php or path.php
               $handler = DOC_ROOT . "/handlers/{$path}/index.php";
               if (!file_exists($handler)) {
                  $handler = DOC_ROOT . "/handlers/{$path}.php";
               }
            }

            // Save unmatched tokens as arguments.
            $arguments[] = array_pop($pieces);
         } while (!file_exists($handler));

         // Put arguments in their proper order.
         array_pop($arguments);
         $arguments = array_filter($arguments);
         $this->arguments = array_reverse($arguments);

         // Quick security check.
         $this->handler = str_replace('..', '', $handler);

         // Cache result for future requests.
         $value = array($this->handler, $this->arguments);
         Cache::set($key, $value, static::$cache_expires);
      }

      // Set a few member variables here for the convenience of the handler.
      $this->method = strtolower($_SERVER['REQUEST_METHOD']);
      $this->post = &$_POST;
      $this->get = &$_GET;
      $this->session = &$_SESSION;
   }

   public function go() {
      $this->parse();

      // Execute.
      require $this->handler;
   }
}

