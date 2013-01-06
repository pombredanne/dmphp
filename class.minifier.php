<?php
/**
 * Together with companion script `tools/build.sh`, minifies and compiles JS,
 * CSS, and LESS resources and then caches the results.
 */

define('MINIFIER_JSON_FILE', '../framework/minifier.json');
define('MINIFIER_CACHE_KEY', 'minifier.data');

class Minifier {
   private static $yui_jar = '../libraries/yuicompressor/build/yuicompressor-*.jar';

   public static function build() {
      assert(substr(getcwd(), -4) == '/web') or die();

      // Remove old minified files.
      self::deleteFiles();

      $results = array();

      // 1. Run yuicompressor.
      $css_count  = self::execute('css');
      $less_count = self::execute('less');
      $js_count   = self::execute('js');

      // 2. Apply the hash prefix.
      $files = shell_exec("find . -name '*.min.css' -or -name '*.min.js'");
      $files = array_filter(explode("\n", $files));
      foreach ($files as $file) {
         $hash = hash_file('crc32', $file);
         $newfile = str_replace('.min.', ".{$hash}.min.", $file);
         rename($file, $newfile);

         // Cleanup the file path and add it to the results.
         $file = str_replace('.min.', '.', $file);
         $results[substr($file, 1)] = substr($newfile, 1);

         if ($less_count > 0) {
            $file = str_replace('.css', '.less', $file);
            $results[substr($file, 1)] = substr($newfile, 1);
         }
      }

      // 3. Update the config file.
      self::writeConfig($results);
   }

   public static function clean() {
      assert(substr(getcwd(), -4) == '/web') or die();

      self::deleteConfig();
      self::deleteFiles();
   }

   public static function minifiedPaths($paths) {
      if (!($config = self::readConfig()))
         return $paths;

      $result = array();

      foreach ($paths as $path) {
         $result[] = @$config->$path ?: $path;
      }

      return $result;
   }

   private static function execute($type) {
      if ($type == 'less') {
         return self::executeLESS();
      }

      $yui_jar = self::$yui_jar;

      // To avoid a yuicompressor bug, run separate conditions for 0, 1, and 2+ files.
      $count = substr_count(shell_exec("find . -name '*.{$type}' | head -2"), "\n");

      if ($count > 1) {
         shell_exec("java -jar {$yui_jar} -o '.{$type}$:.min.{$type}' `find . -name '*.{$type}'`");
      }
      else if ($count == 1) {
         $infile = trim(shell_exec("find . -name '*.{$type}'"));
         $outfile = preg_replace("/\.{$type}$/", ".min.{$type}", $infile);
         shell_exec("java -jar {$yui_jar} -o {$outfile} {$infile}");
      }

      return $count;
   }

   private static function executeLESS() {
      $files = shell_exec("find . -name '*.less'");
      $files = array_filter(explode("\n", $files));

      foreach ($files as $file) {
         $output = preg_replace('/.less$/', '.min.css', $file);
         shell_exec("lessc --yui-compress \"{$file}\" \"{$output}\"");
      }

      return count($files);
   }

   private static function readConfig() {
      if (($data = Cache::get(MINIFIER_CACHE_KEY))) {
         return $data;
      }

      if (($data = @json_decode(@file_get_contents(MINIFIER_JSON_FILE)))) {
         Cache::set(MINIFIER_CACHE_KEY, $data);
      }

      return $data;
   }

   private static function writeConfig($data) {
      file_put_contents(MINIFIER_JSON_FILE, json_encode($data));
      Cache::delete(MINIFIER_CACHE_KEY);
   }

   private static function deleteConfig() {
      @unlink(MINIFIER_JSON_FILE);
      Cache::delete(MINIFIER_CACHE_KEY);
   }

   private static function deleteFiles() {
      shell_exec("find scripts -name '*.min.js' -exec rm {} \;");
      shell_exec("find styles -name '*.min.css' -exec rm {} \;");
   }
}

