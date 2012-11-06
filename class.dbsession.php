<?php
/**
 * Handler for storing PHP sessions in the database instead of on disk.
 * Enabled/disabled via config.ini's `dbsessions` setting.
 */

class DBSession {
   public static function register() {
      ini_set('session.save_handler', 'user');
      session_set_save_handler(
         array('DBSession', 'open'),
         array('DBSession', 'close'),
         array('DBSession', 'read'),
         array('DBSession', 'write'),
         array('DBSession', 'destroy'),
         array('DBSession', 'gc')
      );
   }

   public static function open() {
      $db = Database::getDatabase();
      return $db->isConnected();
   }

   public static function close() {
      return true;
   }

   public static function read($id) {
      $db = Database::getDatabase();
      $sql = "SELECT `data` FROM `sessions` WHERE `id` = ?";
      $query = $db->query($sql, array('i', $id));
      return $db->getValue($query) ?: '';
   }

   public static function write($id, $data) {
      $db = Database::getDatabase();
      $db->query('DELETE FROM `sessions` WHERE `id` = ?', array('i', $id));

      $sql = 'INSERT INTO `sessions` (`id`, `data`, `updated_on`) ' .
             'VALUES (?, ?, UNIX_TIMESTAMP())';
      $db->query($sql, array('is', $id, $data));

      return $db->affectedRows() == 1;
   }

   public static function destroy($id) {
      $db = Database::getDatabase();
      $db->query('DELETE FROM `sessions` WHERE `id` = ?', array('i', $id));
      return $db->affectedRows() == 1;
   }

   public static function gc($max) {
      $db = Database::getDatabase();
      $sql = 'DELETE FROM `sessions` WHERE `updated_on` < UNIX_TIMESTAMP() - ?';
      $db->query($sql, array('i', $max));
      return true;
   }
}

