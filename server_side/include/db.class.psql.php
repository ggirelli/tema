<?php
/**
 * Contains the classes required to contact a PostgreSQL server
 *
 * @author Gabriele Girelli <gabriele@filopoe.it>
 */

/**
 * PostgreSQL ddatabase management
 * @since 0.2.0
 */
class C2SQL {

	// ATTRIBUTES

	/**
	 * PostgreSQL host name
	 * @var string
	 */
	public $host;
	
	/**
	 * PostgreSQL host user
	 * @var string
	 */
	public $user;
	
	/**
	 * PostgreSQL host password
	 * @var string
	 */
	private $pwd;
	
	/**
	 * PostgreSQL database name
	 * @var string
	 */
	public $db_name;
	
	/**
	 * PostgreSQLi instance
	 * @var psql
	 */
	private $psql;
	
	/**
	 * Error variable
	 * @var boolean
	 */
	public $connect_error;
	
	// public FUNCTIONS

	/**
	 * Connects to given database and server
     * @param string host (PostgreSQL host name)
     * @param string dbUser (PostgreSQL host user)
     * @param string dbPass (PostgreSQL host password)
     * @param string dbName (PostgreSQL database name)
	 * @return null
	 */
	public function __construct($host, $user, $pwd, $db_name) {
		$this->host = $host;
		$this->user = $user;
		$this->pwd = $pwd;
		$this->db_name = $db_name;

		// Connect to PostgreSQL server
		$this->connect2PostgreSQL();
	}

	/**
	 * Identifies errors that occurred during the connection.
	 * @return boolean	true = an error has occured
	 */
	public function isError() {
		// Evaluates errors based on the signal variable.
		if( $this->connect_error ) { return true; }
		
		// Evaluate PostgreSQL errors
        $error = pg_last_error($this->psql);
        if ( empty($error) )
            return false;
        else
            return true;
	}
	
	// protected FUNCTIONS

    /**
     * Returns a PostgreSQLresult instance for data fetching
     * @param String $sql 	query to be executed
     * @param Boolean $verbose whether to print an error message
     * @return PostgreSQLresult instance
     */
    protected function & query($sql, $verbose=TRUE) {
        if ( !$psqlResult = pg_query($this->psql, $sql) )
            if ( $verbose ) 
            	trigger_error ('Query fallita: ' . pg_result_error($psqlResult) . ' SQL: ' . $sql);
        $result = new PostgreSQLResult($this, $psqlResult);
		return $result;
    }
	
	/**
	 * Close the connection
	 * @return null
	 */
	protected function close() {
		if( !pg_close($this->psql) )
			trigger_error("Impossible to terminate the connection.\n\n" . pg_last_error($this->psql));
	}

	/**
	 * Check if a given table exists
	 * @param  String $table table name
	 * @return Boolean
	 */
	protected function table_exists($table) {
		$r = $this->query("\dt '" . $table . "'");
		if( $r->size() == 1 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Escapes a string based on current connection's charset
	 * @param  String $s to be escaped
	 * @return String    escaped
	 */
	protected function escape_string($s) {
		return pg_escape_literal($this->psql, $s);
	}

	// private FUNCTIONS
	
	/**
	 * Connects to the database
	 * @return null (sets $this->connect_error)
	 */
	private function connect2PostgreSQL() {
		echo 'connecting';
		if( !$this->psql = @pg_connect('host=' . $this->host . ' user=' . $this->user . ' password=' . $this->pwd .  ' dbname=', $this->db_name) ) {
            trigger_error('Impossible to contact the PostgreSQL server.');
            $this->connectError = true;
		} else {
			echo 'done';
			$this->connectError = false;
		}
	}

}



/**
 * This class manages fetching data from PostgreSQL databases
 * Called by C2SQL->query($sql)
 * @access public
 * @see C2SQL
 * @since 0.2.0
 */
class PostgreSQLresult {
    /**
     * C2SQL instance
     * @var PostgreSQL instance
     */
    var $c2psql;

    /**
     *  Result of the query, to fetch
     * @var resource
     */
    var $result;

    /**
     * @param C2SQL instance $mysql
     * @param resource $result
     * @access public
     */
    public function __construct(& $c2psql,$result) {
        $this->c2psql = & $c2psql;
        $this->result = $result;
    }

    /**
     * Fetches a row
     * @return array 	fetched row
     * @return Boolean 	if no rows are left to be fetched, returns false
     */
    public function fetch () {
        if ( $row = $pg_fetch_assoc($this->result) ) {
            return $row;
        } else if ( $this->size() > 0 ) {
            pg_result_seek($this->result, 0);
            return false;
        } else {
            return false;
        }
    }

    /**
     * @return int 	number of selected rows
     */
    public function size () {
        return pg_num_rows($this->result);
    }

    /**
     * @return int 	ID of the last inserted row
     */
    public function insertID () {
        return pg_last_oid($this->result);
    }
    
    /**
     * Looks for PostgreSQL errors
     * @return Boolean
     */
    public function isError () {
        return $this->c2psql->isError();
    }
}

?>
