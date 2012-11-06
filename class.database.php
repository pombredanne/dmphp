<?php
/**
 * Wrapper around MySQLi to simplify using prepared statements.
 */

class Database {
   private static $me;

   private $mysqli;
   private $affected_rows;
   private $insert_id;

   // Singleton
   public static function getDatabase() {
      if (is_null(self::$me))
         self::$me = new Database();
      return self::$me;
   }

   public function __construct() {
      $this->mysqli = new mysqli(
         Config::get('db.host'),
         Config::get('db.username'),
         Config::get('db.password'),
         Config::get('db.database'),
         Config::get('db.port')
      );
   }

   public function isConnected() {
      return !is_null(@$this->mysqli->host_info);
   }

   public function query($sql, $params = null) {
      if (!$this->isConnected())
         return false;

      // Execute as a non-prepared statement if possible (for performance).
      if (is_null($params)) {
         if (!($result = $this->mysqli->query($sql))) {
            trigger_error("Query failed: ({$this->mysqli->errno}) " .
                          "{$this->mysqli->error}\n\n{$sql}\n\n");
            return false;
         }
         return $result;
      }

      // Prepare.
      if (!($stmt = $this->mysqli->prepare($sql))) {
         trigger_error("Prepare failed: ({$this->mysqli->errno}) " .
                       "{$this->mysqli->error}\n\n{$sql}\n\n");
         return false;
      }

      // Bind.
      $refParams = array();
      foreach ($params as $key => $value) {
         $refParams[$key] = &$params[$key];
      }
      if (!call_user_func_array(array($stmt, 'bind_param'), $params)) {
         trigger_error("Binding parameters failed: ({$stmt->errno}) " .
                       "{$stmt->error}\n\n{$sql}\n\n");
      }

      // Execute.
      if (!$stmt->execute()) {
         trigger_error("Execute failed: ({$stmt->errno}) " .
                       "{$stmt->error}\n\n{$sql}\n\n");
      }

      $is_write = preg_match('/^ *(INSERT|UPDATE|REPLACE|DELETE)/i', $sql);
      if ($is_write) {
         $this->affected_rows = $stmt->affected_rows;
         $this->insert_id = $stmt->insert_id;
      }

      $result = $stmt->get_result();
      $stmt->close();

      return $result;
   }

   public function affectedRows() {
      return $this->affected_rows;
   }

   public function insertId() {
      return $this->insert_id;
   }

   public function numRows($result) {
      return $result->num_rows;
   }

   public function getValue($result) {
      $row = $result->fetch_row();
      return @$row[0];
   }

   public function getValues($result) {
      $column = array();
      $result->data_seek(0);
      while (($row = $result->fetch_row())) {
         $column[] = $row[0];
      }
      return $column;
   }

   public function getRow($result) {
      return $result->fetch_assoc();
   }

   public function getRows($result) {
      $rows = array();
      $result->data_seek(0);
      while (($row = $result->fetch_assoc())) {
         $rows[] = $row;
      }
      return $rows;
   }
}

