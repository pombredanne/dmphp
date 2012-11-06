<?php
/**
 * Routes incoming requests to the appropriate handler.
 */

class Router {
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

      // Dynamically determine which handler to run.
      $pieces = explode('/', substr($uri, 1));

      // Find the handler by traversing up the path.
      // TODO(dmpatierno): Cache results to avoid hitting the disk on every 
      // request.
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
      $this->arguments = array_reverse($arguments);

      // Quick security check.
      $this->handler = str_replace('..', '', $handler);

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

