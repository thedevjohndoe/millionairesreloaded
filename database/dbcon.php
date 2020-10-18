<?php
class dbcon {
    public $last_error;
    public $last_query;
    public $last_result = array();
    public $affected_rows;

    protected $dbconn;
    protected $dbhost;
    protected $dbname;
    protected $dbuser;
    protected $dbpass;

    public function __construct($dbhost, $dbuser, $dbpass, $dbname) {
        $this->dbhost = $dbhost;
        $this->dbname = $dbname;
        $this->dbuser = $dbuser;
        $this->dbpass = $dbpass;

        // Connect to database
        $this->connect();
    }

    public function __destruct() {
        return true;
    }

    public function connect() {
        if(TROUBLESHOOTING) {
            $this->dbconn = mysqli_connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbname);
        } else {
            $this->dbconn = @mysqli_connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbname);
        }

        if(!$this->dbconn) {
            $this->last_error = "Error connecting to the database";
        }
    }

    public function reset() {
        $this->last_error = "";
        $this->last_query = "";
        $this->last_result = array();
        $this->affected_rows = 0;
    }

    public function select($table, $data, $where) {
        if(!is_array($data) || !is_array($where)) {
            return false;
        }

        $columns = $conditions = array();
        foreach($where as $field => $value) {
            // if(is_null($value)) {
                
            // }
            if(!is_numeric($value)) {
                $value  = "'" . $this->dbconn->real_escape_string($value) . "'";
            }

            $conditions[] = "`$field` = $value";
        }

        $columns = implode(", ", $data);
        $conditions = implode(" AND ", $conditions);

        $SQL = "SELECT $columns FROM $table WHERE $conditions";

        $this->query($SQL);
    }

    public function query($SQL) {
        $this->reset();
        $this->last_query = $SQL;

        $response = 0;

        if(!empty($this->dbconn)) {
            $query = $this->dbconn->query($SQL);
        } else {
            return;
        }

        if(preg_match('/^\s*(insert|update)\s/i', $SQL)) {
            $this->affected_rows = $this->dbconn->affected_rows;
            if(preg_match( '/^\s*(insert|replace)\s/i', $query)) {
                $this->insert_id = $this->dbconn->insert_id;
            }

            $response = $this->affected_rows;
        } elseif(preg_match('/^\s*(select)\s/i', $SQL)) {
            if($query->num_rows) {
                while($result = $query->fetch_array()) {
                    $this->last_result[] = $result;
                }

                $response = $query->num_rows;
            }
        }

        return print_r($response);
    }
}
