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
	/**
	 * MySQL host name
	 * @var string
	 */
	var $host;
	
	/**
	 * MySQL host user
	 * @var string
	 */
	var $user;
	
	/**
	 * MySQL host password
	 * @var string
	 */
	var $pwd;
	
	/**
	 * MySQL database name
	 * @var string
	 */
	var $db_name;
	
	/**
	 * Connection id
	 * @var integer
	 */
	var $db_cnx_id;
	
	/**
	 * Error variable
	 * @var boolean
	 */
	var $connect_error;
	
	/**
	 * Connect to given database and server
     * @param string host (MySQL host name)
     * @param string dbUser (MySQL host user)
     * @param string dbPass (MySQL host password)
     * @param string dbName (MySQL database name)
	 * @return null
	 */
	function __construct($host, $user, $pwd, $db_name) {
		$this->host = $host;
		$this->user = $user;
		$this->pwd = $pwd;
		$this->db_name = $db_name;
		
		// Connect to MySQL server
		$this->connect2MySQL();
		// If no errors occurred, connect to database
		if( !$this->isError() ) { $this->connect2MySQL_db(); }
	}
	
	/**
	 * Connect to the server
	 * @return null (sets $this->connect_error)
	 */
	function connect2MySQL() {
		if(!$this->db_cnx_id = @mysql_connect($this->host, $this->user, $this->pwd)) {
            trigger_error('Impossible to contact the MySQL server.');
            $this->connectError = true;
		}
	}
	
	/**
	 * Stabilisce la connessione al database MYSQL selezionato
	 * @return void (set $this->connect_error)
	 * @access private
	 */
	function connect2MySQL_db() {
		if(!@mysql_select_db($this->db_name, $this->db_cnx_id)) {
            trigger_error('Impossibile selezionare il database.');
            $this->connectError = true;
		}
	}
	
	/**
	 * Funzione che individua eventuali errori avvenuti durante la connessione
	 * @return boolean		(true = an error has occured)
	 * @access public
	 */
	function isError() {
		// Valuta gli errori basandosi sulla variabile di segnale
		if( $this->connect_error ) { return true; }
		
		// Valuta eventuali errori di tipo mysql
        $error = mysql_error($this->db_cnx_id);
        if ( empty($error) )
            return false;
        else
            return true;
	}
	
    /**
     * Ritorna un'istanza di MySQLResult per eseguire il fetch delle righe dove
     * @param string sql		(query da eseguire)
     * @return MySQLResult-"class instance"
     * @access public
     */
    function & query($sql) {
        if (!$queryResource = mysql_query($sql,$this->db_cnx_id))
            trigger_error ('Query fallita: ' . mysql_error($this->db_cnx_id) . ' SQL: ' . $sql);
        $result = new MySQLResult($this,$queryResource);
		return $result;
    }
	
	/**
	 * Funzione che blocca la tabella inviata come parametro
	 * @param string table		(tabella da bloccare)
	 * @param class-instance db	(istanza di questa classe)
	 * @access public
	 * @return void
	 */
	function lock($table, $db) {
		// Costruisco la query da eseguire
		$sql = "LOCK TABLES " . $table . " WRITE";
		$db->query($sql);
	}
	
	/**
	 * Funzione che sblocca la tabella inviata come parametro
	 * @param class-instance db	(istanza di questa classe)
	 * @access public
	 * @return void
	 */
	function unlock($db) {
		$sql = "UNLOCK TABLES ";
		$db->query($sql);
	}
	
	/**
	 * Funzione che esegue la chiusura della connessione con il database
	 * @return void		(close mysql-connection)
	 * @access public
	 */
	function close(){
		if( !mysql_close($this->db_cnx_id) )
			trigger_error("Impossibile terminare la connessione con il server MySQL.\n\n" . mysql_error());
	}
}

/**
 * This class manages fetching data from MySQL databases
 * Called by C2MySQL->query($sql)
 * @access public
 * @see C2MySQL
 * @since 0.2.0
 */
class MySQLResult {
    /**
     * Istanza di MySQL che determina la connessione al database
     * @access private
     * @var MySQL-"class instance"
     */
    var $c2mysql;

    /**
     * Risultato della query eseguita, da fetchare
     * @access private
     * @var resource
     */
    var $query;

    /**
     * Funzione costruttore
     * @param object mysql   	(istanza della classe MySQL)
     * @param resource query 	(risultato della MySQL-query eseguita, da fetchare)
     * @access public
     */
    function MySQLResult(& $c2mysql,$query) {
        $this->c2mysql = & $c2mysql;
        $this->query = $query;
    }

    /**
     * Esegue il fetch di una riga
     * @return array ; return boolean(false) (only if the
     * @access public
     */
    function fetch () {
        if ( $row = mysql_fetch_array($this->query,MYSQL_ASSOC) ) {
            return $row;
        } else if ( $this->size() > 0 ) {
            mysql_data_seek($this->query,0);
            return false;
        } else {
            return false;
        }
    }

    /**
     * Ritorna il numero di righe selezionate
     * @return int
     * @access public
     */
    function size () {
        return mysql_num_rows($this->query);
    }

    /**
     * Ritorna l'ID dell'ultima riga inserita
     * @return int
     * @access public
     */
    function insertID () {
        return mysql_insert_id($this->c2mysql->db_cnx_id);
    }
    
    /**
     * Cerca errori di MySQL
     * @return boolean
     * @access public
     */
    function isError () {
        return $this->c2mysql->isError();
    }
}

?>