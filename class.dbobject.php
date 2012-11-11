<?php
/**
 * Superclass for database-backed objects.
 *
 * Implementations must define the static table variable and contain an `id` 
 * field as an auto-incremented integer primary key.
 */

class DBObject {
   protected $_data = array();
   private static $table = null;

   public function __construct($id_or_data = null) {
      // Use supplied data...
      if (is_array($id_or_data)) {
         $this->_data = $id_or_data;
      }
      // ...or pull from the database.
      else if ($id_or_data) {
         $db = Database::getDatabase();
         $table = static::$table;

         $sql = <<<EOT
            SELECT * FROM `{$table}`
            WHERE `id` = ?
EOT;
         $query = $db->query($sql, array('i', $id_or_data));
         $this->_data = $db->getRow($query);
      }
   }

   public function __get($key) {
      return isset($this->_data[$key]) ? $this->_data[$key] : false;
   }

   public function __set($key, $value) {
      $this->_data[$key] = $value;
   }

   // Find all, find one, or find with query suffix.
   public static function find($id = false, $suffix = '') {
      $class = get_called_class();

      // Find by id.
      if ($id !== false) {
         $object = new $class((int)$id);
         return $object->id ? $object : false;
      }

      // Find all.
      $db = Database::getDatabase();
      $query = $db->query('SELECT * FROM ' . static::$table . " {$suffix}");
      $rows = $db->getRows($query);

      $result = array();
      foreach ($rows as $row) {
         $result[] = new $class($row);
      }
      return $result;
   }

   public static function deleteWithId($id) {
      $db = Database::getDatabase();
      $query = 'DELETE FROM ' . static::$table . ' WHERE `id` = ?';
      $db->query($query, array('i', $id));
   }

   public function delete() {
      self::deleteWithId($this->id);
   }

   public function save() {
      $class = get_called_class();
      $db = Database::getDatabase();

      // Build the parameter list.
      $set = '';
      $params = array('');
      foreach ($this->_data as $key => $value) {
         $set .= "`{$key}` = ?,";
         $params[0] .= 's';
         $params[] = $value;
      }

      if (!$set) {
         return false;
      }

      // Combine insert or update in one query.
      $sql = 'INSERT INTO ' . static::$table . ' SET ' .
         substr($set, 0, -1) . ' ON DUPLICATE KEY UPDATE ' .
         substr($set, 0, -1);

      if (!$db->query($sql, $params))
         return false;

      if ($id = $db->insertId())
         $this->id = $id;

      return true;
   }
}

