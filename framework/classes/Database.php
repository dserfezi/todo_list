<?php
class Database
{

    private $connection;
    private static $instance;


    private function __construct(){
        $this->connect();
    }


    public static function getInstance() {
        if(!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    public function getConnectionInstance(){

        return $this->connection;

    }


    private function __clone(){}


    /**
     * Make a connection to database
     */
    private function connect(){

        if(!isset($this->connection)) {
            $config = require __DIR__.'/../../app/config/database.php';
            $mysql = $config['mysql'];
            $this->connection = mysqli_connect($mysql['hostname'], $mysql['username'], $mysql['password'], $mysql['database']);
            $this->connection->set_charset($mysql['charset']);
        }

        if($this->connection===false) {
            die(mysqli_connect_error());
        }

    }


    /**
     * Perform query on database
     * @param $query
     * @return bool|mysqli_result
     */
    public function runQuery($query){

        // Query the database
        $result = mysqli_query($this->connection, $query) or trigger_error("SQL query failed: " . mysqli_error($this->connection));
        return $result;

    }


    /**
     * Select data from database
     * @param $query
     * @return array|bool
     */
    public function runSelect($query){

        $rows = array();
        $result = $this->runQuery($query);
        if($result === false) {
            return false;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }

        return $rows;

    }


    /**
     * Escape values before using in query
     * @param $value
     * @return string
     */
    public function runEscape($value) {

        $escaped = mysqli_real_escape_string($this->connection, $value);
        return $escaped;

    }

}
?>