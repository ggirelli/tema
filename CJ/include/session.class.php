<?php

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

	public function __construct() {
		// Silence is golden!
	}

	public function init() {
		if($this->id != -1) {
			// Initialize a new session into the current instance
		} else {
			return false;
		}
	}
	
	public function load($id) {
		if($this->id != -1) {
			// Return a new SOGIsession instance
		} else {
			// Load into the current instance
		}
	}

}

?>