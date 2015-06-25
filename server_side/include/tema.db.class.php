<?php

// Requirements
require_once('db.class.php');

/**
* Manages TEMA database
* @author Gabriele Girelli <gabriele@filopoe.it>
* @since 0.2.0
*/
class TEMAdb extends C2SQL {
	
	// public FUNCTIONS

	public function __construct($host, $user, $pwd, $db_name) {
		// Connect
		parent::__construct($host, $user, $pwd, $db_name);

		if( !parent::isError() ) {

			// If needed, initialize the database
			if( !$this->check_database() ) {
				$this->init_database();
			}

		} else {
			die('Impossible to contact the pSQL server.');
		}
	}

	// private FUNCTIONS

	/**
	 * Checks for database errors
	 * @return Boolean
	 */
	private function check_database() {
		if( 
			!parent::table_exists('sessions') or
			!parent::table_exists('sessions_settings') or
			!parent::table_exists('sessions_users') or
			!parent::table_exists('sessions_history') or
			!parent::table_exists('sessions_shared')
		) {
			return false;
		}
		
		return true;
	}

	/**
	 * Initializes database
	 * @return null
	 */
	private function init_database() {

		if ( !parent::table_exists('sessions') ) { 
			// Session table definition
			$sql = "CREATE TABLE sessions (" .
				"id SERIAL, " .
				"seed VARCHAR(100) NOT NULL UNIQUE, " .
				"folder_path VARCHAR(200) NOT NULL UNIQUE, " .
				"interface_uri VARCHAR(200) NOT NULL UNIQUE, " .
				"owner INTEGER NOT NULL, " .
				"title VARCHAR(200) NOT NULL, " .
				"privacy VARCHAR(100) NOT NULL, " .
				"password VARCHAR(100), " .
				"running INTEGER NOT NULL DEFAULT 0, " .
				"last_query VARCHAR(100), " .
				"last_query_when TIMESTAMP, " .
				"current_net VARCHAR(100) " .
				", PRIMARY KEY(id))";
			
			// Create table
			parent::query($sql);
			if( parent::isError() ) {
				die('An error occurred while initializing the MySQL database.');
			}
		}

		if ( !parent::table_exists('sessions_settings') ) {
			// Sessions_settings table definition
			$sql = "CREATE TABLE sessions_settings (" .
				"id SERIAL, " .
				"seed VARCHAR(100) NOT NULL, " .
				"setting_key VARCHAR(200) NOT NULL, " .
				"setting_value TEXT NOT NULL " .
				", PRIMARY KEY(id))";
			
			// Create table
			parent::query($sql);
			if( parent::isError() ) {
				die('An error occurred while initializing the MySQL database.');
			}
		}

		if ( !parent::table_exists('sessions_users') ) {
			// Sessions_users table definition
			$sql = "CREATE TABLE sessions_users (" .
				"id SERIAL, " .
				"nickname VARCHAR(100) NOT NULL UNIQUE, " .
				"email VARCHAR(100) NOT NULL UNIQUE, " .
				"password VARCHAR(200) NOT NULL, " .
				"confirm_token VARCHAR(100) NOT NULL UNIQUE, " .
				"token_when TIMESTAMP, " .
				"confirmed INTEGER NOT NULL " .
				", PRIMARY KEY(id))";
			
			// Create table
			parent::query($sql);
			if( parent::isError() ) {
				die('An error occurred while initializing the MySQL database.');
			}
		}

		if ( !parent::table_exists('sessions_history') ) {
			// Sessions_history table definition
			$sql = "CREATE TABLE sessions_history (" .
				"id SERIAL, " .
				"user_id INTEGER NOT NULL, " .
				"seed VARCHAR(100) NOT NULL, " .
				"date TIMESTAMP DEFAULT NOW()" .
				", PRIMARY KEY(id))";

			// Create table
			parent::query($sql);
			if( parent::isError() ) {
				die('An error occurred while initializing the MySQL database');
			}
		}

		if ( !parent::table_exists('sessions_shared') ) {
			// Sessions_shared table definition
			$sql = "CREATE TABLE sessions_shared (" .
				"id SERIAL, " .
				"user_id INTEGER NOT NULL, " .
				"seed VARCHAR(100) NOT NULL" .
				", PRIMARY KEY(id))";

			// Create table
			parent::query($sql);
			if( parent::isError() ) {
				die('An error occurred while initializing the MySQL database');
			}
		}
	}

	/**
	 * Perform a one-way hashing of provided string
	 * @param  String $s
	 * @return String    Hashed version of $s
	 */
	protected function encrypt($s) {
		return(md5('TEMA' . sha1($s . md5('TEMA')) . sha1('TEMA')));
	}
}

?>
