<?php

// Requirements
require_once('db.class.php');

/**
* Manages SOGI database
* @author Gabriele Girelli <gabriele@filopoe.it>
* @since 0.2.0
*/
class SOGIdb extends C2MySQL {
	
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
			die('Impossible to contact the MySQL server.');
		}
	}

	// private FUNCTIONS

	/**
	 * Checks for database errors
	 * @return Boolean
	 */
	private function check_database() {
		if( !parent::table_exists('sessions') or !parent::table_exists('sessions_settings') ) {
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
				"id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY, " .
				"seed VARCHAR(100) NOT NULL UNIQUE, " .
				"folder_path VARCHAR(200) NOT NULL UNIQUE, " .
				"interface_uri VARCHAR(200) NOT NULL UNIQUE, " .
				"running INT NOT NULL DEFAULT 0, " .
				"last_query VARCHAR(100), " .
				"last_query_when INTEGER, " .
				"current_net VARCHAR(100), " .
				"node_thr INTEGER, " .
				"sif_sample_col VARCHAR(100)" .
				")";
			
			// Create table
			parent::query($sql);
			if( parent::isError() ) {
				die('An error occurred while initializing the MySQL database.');
			}
		}

		if ( !parent::table_exists('sessions_settings') ) {
			// Session_settings table definition
			$sql = "CREATE TABLE sessions_settings (" .
				"id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY, " .
				"seed VARCHAR(100) NOT NULL, " .
				"setting_key VARCHAR(200) NOT NULL, " .
				"setting_value TEXT NOT NULL " .
				")";
			
			// Create table
			parent::query($sql);
			if( parent::isError() ) {
				die('An error occurred while initializing the MySQL database.');
			}
		}
	}
}

?>
