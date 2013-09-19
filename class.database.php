<?php
/**
 * Wrapper around PDO to simplify using prepared statements.
 */

class Database {
   private static $me;

   private $pdo;
   private $is_connected;
   private $affected_rows;
   private $insert_id;

   // Singleton
   public static function getDatabase() {
      if (is_null(self::$me))
         self::$me = new Database();
      return self::$me;
   }

   public function __construct() {
      $this->pdo = new PDO(
         Config::get('db.dsn'),
         @Config::get('db.username'),
         @Config::get('db.password')
      );

      if ($this->pdo) {
        $this->is_connected = true;
      }
   }

   public function isConnected() {
      return $this->is_connected;
   }

   public function query($sql, $params = null, $types = null) {
      if (!$this->isConnected())
         return false;

      // Execute as a non-prepared statement if possible (for performance).
      if (is_null($params)) {
         if (!($result = $this->pdo->query($sql))) {
            $error = $this->pdo->errorInfo();
            trigger_error("Query failed: ({$error[0]}) " .
                          "{$error[2]}\n\n{$sql}\n\n");
            return false;
         }
         return $result;
      }

      // Prepare.
      if (!($stmt = $this->pdo->prepare($sql))) {
         $error = $this->pdo->errorInfo();
         trigger_error("Prepare failed: ({$error[0]}) " .
                       "{$error[2]}\n\n{$sql}\n\n");
         return false;
      }

      // Bind.
      $isAssociative = array_is_assoc($params);
      foreach ($params as $key => &$value) {
         $type = self::PDOType(@$types[$key]);
         $key = $isAssociative ? $key : $key + 1;
         if (!$stmt->bindParam($key, $value, $type)) {
            $error = $this->pdo->errorInfo();
            trigger_error("Binding parameters failed: ({$error[0]}) " .
                          "{$error[2]}\n\n{$sql}\n\n");
         }
      }

      // Execute.
      if (!$stmt->execute()) {
         $error = $this->pdo->errorInfo();
         trigger_error("Execute failed: ({$error[0]}) " .
                       "{$error[2]}\n\n{$sql}\n\n");
      }

      $is_write = preg_match('/^ *(INSERT|UPDATE|REPLACE|DELETE)/i', $sql);
      if ($is_write) {
         $this->affected_rows = $stmt->rowCount();
         $this->insert_id = $this->pdo->lastInsertId();
      }

      return $stmt;
   }

   public function affectedRows() {
      return $this->affected_rows;
   }

   public function insertId() {
      return $this->insert_id;
   }

   public function getValue($stmt) {
      return $stmt->fetchColumn();
   }

   public function getValues($stmt) {
      return $stmt->fetchAll(PDO::FETCH_COLUMN);
   }

   public function getRow($stmt) {
      return $stmt->fetch(PDO::FETCH_ASSOC);
   }

   public function getRows($result) {
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
   }

   private function PDOType($char) {
      switch ($char) {
      case 'b': return PDO::PARAM_BOOL;
      case 'n': return PDO::PARAM_NULL;
      case 'i': return PDO::PARAM_INT;
      case 'l': return PDO::PARAM_LOB;
      case 's': default: return PDO::PARAM_STR;
      }
   }
}

