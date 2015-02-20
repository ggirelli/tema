<?php
/**
 * Contains the classes required to contact a MySQL server
 *
 * @author Gabriele Girelli <gabriele@filopoe.it>
 */

/**
 * MySQL ddatabase management
 * @since 0.2.0
 */
class C2MySQL {

	// ATTRIBUTES

	/**
	 * MySQL host name
	 * @var string
	 */
	public $host;
	
	/**
	 * MySQL host user
	 * @var string
	 */
	public $user;
	
	/**
	 * MySQL host password
	 * @var string
	 */
	private $pwd;
	
	/**
	 * MySQL database name
	 * @var string
	 */
	public $db_name;
	
	/**
	 * MySQLi instance
	 * @var mysqli
	 */
	private $mysqli;
	
	/**
	 * Error variable
	 * @var boolean
	 */
	public $connect_error;
	
	// public FUNCTIONS

	/**
	 * Connects to given database and server
     * @param string host (MySQL host name)
     * @param string dbUser (MySQL host user)
     * @param string dbPass (MySQL host password)
     * @param string dbName (MySQL database name)
	 * @return null
	 */
	public function __construct($host, $user, $pwd, $db_name) {
		$this->host = $host;
		$this->user = $user;
		$this->pwd = $pwd;
		$this->db_name = $db_name;
		
		// Connect to MySQL server
		$this->connect2MySQL();
		// If no errors occurred, connect to database
		if( !$this->isError() ) { $this->connect2MySQL_db(); }
	}
	
	// protected FUNCTIONS

    /**
     * Returns a MySQLresult instance for data fetching
     * @param String $sql 	query to be executed
     * @return MySQLresult instance
     */
    protected function & query($sql) {
        if ( !$mysqliResult = $this->mysqli->query($sql) )
            trigger_error ('Query fallita: ' . $this->mysqli->error . ' SQL: ' . $sql);
        $result = new MySQLResult($this,$mysqliResult);
		return $result;
    }
	
	/**
	 * Locks a table
	 * @param String $table 	table name
	 * @param db instance 	$db
	 * @return null
	 */
	protected function lock($table, $db) {
		// Construct the query
		$sql = "LOCK TABLES " . $table . " WRITE";
		$db->query($sql);
	}
	
	/**
	 * Unlocks all tables
	 * @param db instance 	$db
	 * @return null
	 */
	protected function unlock($db) {
		$sql = "UNLOCK TABLES ";
		$db->query($sql);
	}
	
	/**
	 * Close the connection
	 * @return null
	 */
	protected function close() {
		if( !$this->mysqli->close() )
			trigger_error("Impossible to terminat the connection.\n\n" . $this->mysqli->error);
	}

	/**
	 * Check if a given table exists
	 * @param  String $table table name
	 * @return Boolean
	 */
	protected function table_exists($table) {
		$r = $this->query("SHOW TABLES LIKE '" . $table . "'");
		if( $r->size() == 1 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Identifies errors that occurred during the connection.
	 * @return boolean	true = an error has occured
	 */
	protected function isError() {
		// Evaluates errors based on the signal variable.
		if( $this->connect_error ) { return true; }
		
		// Evaluate MySQL errors
        $error = $this->mysqli->error;
        if ( empty($error) )
            return false;
        else
            return true;
	}

	// private FUNCTIONS
	
	/**
	 * Connects to the server
	 * @return null (sets $this->connect_error)
	 */
	private function connect2MySQL() {
		if( !$this->mysqli = @mysqli::__construct($this->host, $this->user, $this->pwd) ) {
            trigger_error('Impossible to contact the MySQL server.');
            $this->connectError = true;
		}
	}
	
	/**
	 * Connects to the database
	 * @return void (sets $this->connect_error)
	 */
	private function connect2MySQL_db() {
		if( !@$this->mysqli->select_db($this->db_name) ) {
            trigger_error('Impossible to select the database.');
            $this->connectError = true;
		}
	}

}



/**
 * This class manages fetching data from MySQL databases
 * Called by C2MySQL->query($sql)
 * @access public
 * @see C2MySQL
 * @since 0.2.0
 */
class MySQLresult {
    /**
     * C2MySQL instance
     * @var MySQL instance
     */
    var $c2mysql;

    /**
     *  Result of the query, to fetch
     * @var resource
     */
    var $result;

    /**
     * @param C2MySQL instance $mysql
     * @param resource $result
     * @access public
     */
    public function __construct(& $c2mysql,$result) {
        $this->c2mysql = & $c2mysql;
        $this->result = $result;
    }

    /**
     * Fetches a row
     * @return array 	fetched row
     * @return Boolean 	if no rows are left to be fetched, returns false
     */
    public function fetch () {
        if ( $row = $this->result->fetch_array(MYSQLI_ASSOC) ) {
            return $row;
        } else if ( $this->size() > 0 ) {
            $this->result->data_seek(0);
            return false;
        } else {
            return false;
        }
    }

    /**
     * @return int 	number of selected rows
     */
    public function size () {
        return $this->result->num_rows;
    }

    /**
     * @return int 	ID of the last inserted row
     */
    public function insertID () {
        return $this->c2mysql->mysqli->insert_id;
    }
    
    /**
     * Looks for MySQL errors
     * @return Boolean
     */
    public function isError () {
        return $this->c2mysql->isError();
    }
}

?>
