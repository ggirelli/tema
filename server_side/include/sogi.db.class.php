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
		if( !parent::table_exists('sessions') ) {
			return false;
		}
	}

	/**
	 * Initializes database
	 * @return null
	 */
	private function init_database() {

		// Session table definition
		$sql = "CREATE TABLE sessions (" .
			"id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY, " .
			"path VARCHAR(200) NOT NULL UNIQUE, " .
			"uri VARCHAR(200) NOT NULL UNIQUE, " .
			"running INT NOT NULL DEFAULT 0, " .
			"lastOperation VARCHAR(100), " .
			"time INTEGER, " .
			"currNet VARCHAR(100), " .
			"nodeThr INTEGER" .
			")";
		
		// Create table
		parent::query($sql);
		if( parent::isError() ) {
			die('An error occurred while initializing the MySQL database.');
		}
	}
}

?>