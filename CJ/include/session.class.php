<?php

/**
 * Manages SOGI sessions.
 * 
 * @author Gabriele Girelli <gabriele@filopoe.it>
 * @copyright Gabriele Girelli 2014
 * @version 0.0.1
 */
class SOGIsession {
	
	/**
	 * SOGI session ID
	 * @var integer
	 */
	private $id = -1;

	/**
	 * Back-end path to the session folder
	 * @var String
	 */
	private $folder_path;

	/**
	 * Front-end uri to the session folder
	 * @var String
	 */
	private $folder_uri;

	/**
	 * If processes are running in this SOGI session
	 * @var Boolean
	 */
	private $running;

	/**
	 * Last query sent to the server by the client
	 * @var String
	 */
	private $last_query;

	/**
	 * When the last query was sent
	 * @var TIMESTAMP
	 */
	private $last_query_when;

	/*-----------*/
	/* FUNCTIONS */
	/*-----------*/

	/**
	 * Loads a SOGIsession or creates a new one.
	 */
	public function __construct($id = null) {
		if(is_null($id)) {
			$this->init();
		} else {
			$this->load($id);
		}
	}

	/**
	 * Initialize a new SOGIsession in the current instance, if empty.
	 * @return Boolean False if the current instance is not empty.
	 */
	public function init() {
		if($this->id != -1) {
			// Initialize a new session into the current instance
		} else {
			return false;
		}
	}
	
	/**
	 * Load a SOGIsession in the current instance, if empty, or returns a new SOGIsession class instance.
	 * @param  int $id ID of the SOGIsession to load
	 * @return SOGIsession     A new SOGIsession instance if the current one is not empty.
	 */
	public function load($id) {
		if($this->id != -1) {
			// Return a new SOGIsession instance
		} else {
			// Load into the current instance
		}
	}

	/**
	 * Retrieve the value of a parameter, in the current session.
	 * @param  String $attr Attribute name
	 * @return mix       The value of the attribute
	 */
	public function get($attr) {
		// Return attribute value
	}

	/**
	 * Retrieves the actual file list for the current session.
	 * @return array List of file names
	 */
	private function getCurrFileList() {
		// Return the current list of file names
	}

	/**
	 * Writes a config file with all the session params.
	 * @return Boolean T if success
	 */
	private function writeSession() {

	}

	/**
	 * Reads a session from its config file
	 * @param  int $id The ID of the session to read
	 * @return array     Session attributes
	 */
	private function readSession($id) {

	}

}

?>