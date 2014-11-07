<?php

// Requirements
require_once('functions.lib.php');
require_once('sogi.db.class.php');

/**
* Class that manages SOGI sessions
* @author Gabriele Girelli <gabriele@filopoe.it>
* @since 0.2.0
*/
class SOGIsession extends SOGIdb {
	
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
	private $folder_path;

	/**
	 * User-side URI to the session contents
	 * @var String
	 */
	private $interface_uri;

	/**
	 * Is an operation running (server-side) for the current session?
	 * @var Boolean
	 */
	private $running;

	/**
	 * Label of the last operation run for the current session
	 * @var String
	 */
	private $last_query;

	/**
	 * Unix-based time of the last operation launched for the current session
	 * @var integer
	 */
	private $last_query_when;

	/**
	 * Name of the network currently loaded in the visualization canvas
	 * @var String
	 */
	private $current_net;

	/**
	 * Max number of nodes visualized in the canvas
	 * @var integer
	 */
	private $node_thr;

	// public FUNCTIONS

	/**
	 * Connects with the server.
	 * @param String $host    MySQL host address
	 * @param String $user    MySQL user
	 * @param String $pwd     MySQL password
	 * @param String $db_name Database name
	 */
	public function __construct($host, $user, $pwd, $db_name) {
		parent::__construct($host, $user, $pwd, $db_name);
	}

	/**
	 * Initialize class behaviour based on the given id.
	 * @param  String $id session id
	 * @return null
	 */
	public function init($id) {
		if( $this->exists($id) ) {

			// Load old session in current class instance
			$this->_load($id);

		} else {

			// Create new session and load it in the current class instance
			$this->_new($id);

		}
	}

	/**
	 * Returns the value of an attribute
	 * @param  String $k attribute key
	 * @return String    the value as String
	 */
	public function get($k) {
		switch($k) {
			case 'id': {
				return $this->id;
				break;
			}
			case 'folder_path': {
				return $this->folder_path;
				break;
			}
			case 'interface_uri': {
				return $this->interface_uri;
				break;
			}
			case 'running': {
				return $this->running;
				break;
			}
			case 'last_query': {
				return $this->last_query;
				break;
			}
			case 'last_query_when': {
				return $this->last_query_when;
				break;
			}
			case 'current_net': {
				return $this->current_net;
				break;
			}
			case 'node_thr': {
				return $this->node_thr;
				break;
			}
		}
	}

	/**
	 * Determines whether a certain SOGIsession exists
	 * @param  String $id
	 * @return boolean
	 */
	public function exists($id) {
		$r = $this->query("SELECT id FROM sessions WHERE seed = '" . $id . "'");
		if( 1 == $r->size() ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Proposes a new session id that's not been used yet.
	 * @return String new session id
	 */
	public function new_id() {
		// Prepare new $id
		$id = time() . random_string(10);
		// Change $id until it's new
		while($this->exists($id)) {
			$id = time() . random_string(10);
		}

		return $id;
	}

	// private FUNCTIONS

	/**
	 * Creates a new session and loads it in the current class instance.
	 * @param  String $id
	 * @return null
	 */
	private function _new($id) {
		if ( !$this->exists($id) ) {

			// Make session directory
			mkdir(SPATH . '/' . $id);

			// Insert session in the database
			$sql = "INSERT INTO sessions (seed, folder_path, interface_uri, running) VALUES ( " .
				"'" . $id . "', " .
				"'" . SPATH . "/" . $id . "', " .
				"'" . RURI . "/s/" . $id . "', " .
				"0)";
			$this->query($sql);

			// Load session
			$this->_load($id);
		}
	}

	/**
	 * Loads an existing session in the current class instance.
	 * @param  String $id
	 * @return null
	 */
	private function _load($id) {
		if ( $this->exists($id) ) {
			$sql = "SELECT * FROM sessions WHERE seed = '" . $id . "'";
			$q = $this->query($sql);
			$q = $q->fetch();

			$this->id = $q['seed'];
			$this->folder_path = $q['folder_path'];
			$this->interface_uri = $q['interface_uri'];
			$this->running = $q['running'];
			$this->last_query = $q['last_query'];
			$this->last_query_when = $q['last_query_when'];
			$this->current_net = $q['current_net'];
			$this->node_thr = $q['node_thr'];
		}
	}

}
?>
