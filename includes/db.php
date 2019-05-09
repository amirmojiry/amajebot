<?php
require_once('defining.php');

class Database {
  private $_connection;

  //store the single instance.
  private static $_instance;

  /**
  * Get an instance of the Database.
  * @return Database
  */
  public static function getInstance ($option = null) {
    if (self::$_instance == null) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  /**
  * Constructor
  */
  public function __construct () {
    $host_name = DB_HOST;
    $username = DB_USER;
    $password = DB_PASS;
    $db_name = DB_NAME;
    $this->_connection = new mysqli ($host_name, $username, $password, $db_name);
    if (mysqli_connect_error()) {
      echo "Connection failed: " . mysqli_connect_error();
      exit;
    }
    $this ->_connection ->query ("SET NAMES 'utf8'");
  }

  /**
  * Safe Query
  * @param string $sql
  * @param array $data
  */
  private function _safeQuery ( &$sql, $data) {
    foreach ($data as $key => $value) {
      $value = $this ->_connection ->real_escape_string ($value);
      $value = "'$value'";
      $sql = str_replace (":$key", $value, $sql);
    }
    return $this->_connection->query ($sql);
  }

  /**
  * Modify sql query.
  * @param string $sql
  * @param array $data
  * @return $result
  */
  public function modify ( $sql, $data = array() ) {
    $result = $this ->_safeQuery ($sql, $data);
    if (!$result) {
      echo "Query: " . $sql . " failed due to " . mysqli_error($this->_connection);
      exit;
    }
    return $result;
  }

  /**
  * Insert into database
  * @param string $sql
  * @param array $data
  * @return int $lastId
  */
  public function insert ($sql, $data = array() ) {
    $result = $this->_safeQuery($sql, $data);
    if (!$result) {
      echo "Query: " . $sql . " failed due to " . mysqli_error($this->_connection);
      exit;
    }
    $lastId = mysqli_insert_id($this->_connection);
    return $lastId;
  }

  /**
  * Insert into database
  * @param string $sql
  * @param array $data
  * @return array $records
  */
  public function query ($sql, $data = array() ) {
    $result = $this->_safeQuery($sql, $data);
    if (!$result) {
      echo "Query: " . $sql . " failed due to " . mysqli_error($this->_connection);
      exit;
    }
    $records = array();
    if ($result->num_rows == 0) {
      return $records;
    }
    while ($row = $result -> fetch_assoc()) {
      $records[] = $row;
    }
    return $records;
  }

  /**
  * Connect to database.
  * @return $connection
  */
  public function connection () {
    return $this->_connection;
  }

  /**
  * Close database connection.
  */
  public function close () {
    $this->_connection->close();
  }
}
