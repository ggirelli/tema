<?php

/**
* Class that manages SOGI sessions
* @author Gabriele Girelli <gabriele@filopoe.it>
* @since 0.2.0
*/
class SOGIsession extends C2MySQL {
	
	// ATTRIBUTES
	
	/**
	 * ID of the loaded session
	 * @var String
	 */
	private $id;

	/**
	 * Server-side path to the session directory
	 * @var String
	 */
	private $path;

	/**
	 * User-side URI to the session contents
	 * @var String
	 */
	private $uri;

	/**
	 * Is an operation running (server-side) for the current session?
	 * @var Boolean
	 */
	private $running;

	/**
	 * Label of the last operation run for the current session
	 * @var String
	 */
	private $lastOperation;

	/**
	 * Unix-based time of the last operation launched for the current session
	 * @var integer
	 */
	private $lastTime;

	/**
	 * Name of the network currently loaded in the visualization canvas
	 * @var String
	 */
	private $currNetwork;

	/**
	 * Max number of nodes visualized in the canvas
	 * @var integer
	 */
	private $nodeThr;

	// public FUNCTIONS

	public function __construct($id) {
		# code...
	}

	/**
	 * Returns the value of an attribute
	 * @param  String $k attribute key
	 * @return String    the value as String
	 */
	public function get($k) {

	}

	// private FUNCTIONS

	/**
	 * Creates a new session and loads it in the current class instance.
	 * @param  String $id
	 * @return null
	 */
	private function new($id) {

	}

	/**
	 * Loads an existing session in the current class instance.
	 * @param  String $id
	 * @return null
	 */
	private function load($id) {

	}

	// static FUNCTIONS
	
	/**
	 * Determines whether a certain SOGIsession exists
	 * @param  String $id
	 * @return boolean
	 */
	public static function exists($id) {

	}

	/**
	 * Proposes a new session id that's not been used yet.
	 * @return String new session id
	 */
	public static function new_id() {

	}

}
?>
