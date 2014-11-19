<?php

// Requirements
require_once('functions.lib.php');
require_once('tea.db.class.php');

/**
* Class that manages TEA sessions
* @author Gabriele Girelli <gabriele@filopoe.it>
* @since 0.2.0
*/
class TEAsession extends TEAdb {
	
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

	/**
	 * List of networks with conversion status
	 * @var stdClass
	 */
	private $network_list;

	/**
	 * Settings labels
	 * @var array
	 */
	private $settings_labels = array(
		'sif_sample_col',
		'node_thr'
	);

	/**
	 * Settings
	 * @var stdClass
	 */
	private $settings;

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
			case 'network_list': {
				$this->list_networks();
				return $this->network_list;
				break;
			}
			case 'settings': {
				return $this->settings;
				break;
			}
			default: return null;
		}
	}

	/**
	 * Sets session attributes
	 * @param String $k attribute label
	 * @param mix $v attribute value
	 */
	public function set($k, $v) {
		switch($k) {
			case 'sif_sample_col': {
				$this->sif_sample_col = mysqli::escape_string($v);
				break;
			}
		}
 	}

	/**
	 * Determines whether a certain TEAsession exists
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

	/**
	 * Executes a shell query
	 * @param  String $name         Query action name
	 * @param  String $query        Shell query
	 * @return Boolean              Whether everything went smoothly
	 */
	public function exec($name, $query) {
		if ( 0 == $this->running ) {
			// Update session attributes
			$this->running = 1;
			$this->last = $name;
			$this->time = time();
			$this->_dump();

			exec($query, $res);

			$this->running = 0;
			$this->_dump();

			return(TRUE);
		} else {
			return FALSE;
		}
	}

	/**
	 * Executes a shell query
	 * @param  String $name         Query action name
	 * @param  String $query        Shell query
	 * @return mix 					The result of the query
	 * @return null 				If already running
	 */
	public function exec_return($name, $query) {
		if ( 0 == $this->running ) {
			// Update session attributes
			$this->running = 1;
			$this->last = $name;
			$this->time = time();
			$this->_dump();

			exec($query, $res);

			$this->running = 0;
			$this->_dump();

			return($res);
		} else {
			return null;
		}
	}


	public function apply_settings($settings) {
		$updated = false;
		foreach ($this->settings_labels as $label) {
			if ( isset($settings[$label]) ) {
				$this->settings[$label] = $settings[$label];
				$updated = true;
			}
		}
		
		if ( $updated ) {
			$this->write_settings();
		}
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
			$this->set_default_settings($id);

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

			$this->list_networks();
			$this->read_settings();
		}
	}

	/**
	 * Update the session attributes on the database
	 * @return Boolean If an error occured
	 */
	private function _dump() {
		$sql = "UPDATE sessions SET " .
			"folder_path='" . $this->folder_path . "', " .
			"interface_uri='" . $this->interface_uri . "', " .
			"running='" . $this->running . "', " .
			"last_query='" . $this->last_query . "', " .
			"last_query_when='" . $this->last_query_when . "', " .
			"current_net='" . $this->current_net . "', " .
			"node_thr='" . $this->node_thr . "' " .
			"WHERE seed='" . $this->id . "'";

		parent::query($sql);
		return parent::isError();
	}

	/**
	 * Updates the network_list of the current session
	 * @return null
	 */
	private function list_networks() {

		// Check file list
		$file_list = glob($this->folder_path . '/*');
		if ( 0 == count($file_list) ) $this->network_list = new stdClass;

		// Clean file list
		$network_list = array();
		foreach ($file_list as $file) {
			// Remove path
			$file = explode('/', $file);
			$file = $file[count($file) - 1];

			if ( !in_array($file, $GLOBALS['FILENAME_BAN']) ) {
				$file_exp = explode('.', $file);

				// Get extension
				$ext = $file_exp[count($file_exp) - 1];

				// Get non-extension
				$file_name = $file_exp;
				array_splice($file_name, count($file_name) - 1, 1);
				$file_name = implode('.', $file_name);

				// Add to $network_list (0: to-convert, 1: converted)
				if ( isset($network_list[$file_name]) ) {
					if ( 'json' == $ext ) {
						$network_list[$file_name]->status = 1;
					} elseif ( 'dat' == $ext ) {
						$network_list[$file_name]->data = file_get_contents($this->folder_path . '/' . $file_name . '.dat');
					}
				} else {
					if ( 'json' == $ext ) {
						$network_list[$file_name] = new stdClass;
						$network_list[$file_name]->status = 1;
					} elseif ( 'dat' == $ext ) {
						$network_list[$file_name] = new stdClass;
						$network_list[$file_name]->data = file_get_contents($this->folder_path . '/' . $file_name . '.dat');

					} elseif ( 'graphml' == $ext) {
						$network_list[$file_name] = new stdClass;
						$network_list[$file_name]->status = 0;
					}
				}
			}
		}
		$this->network_list = $network_list;
	}

	/**
	 * Reads the settings from the database
	 */
	private function read_settings() {
		$sql = "SELECT * FROM sessions_settings WHERE seed = '" . $this->id . "'";
		$q = $this->query($sql);

		$this->settings = array();
		while ( $row = $q->fetch() ) {
			$this->settings[$row['setting_key']] = $row['setting_value'];
		}

		foreach ($this->settings_labels as $label) {
			if ( !isset($this->settings[$label]) ) {
				$this->settings[$label] = null;
			}
		}
	}

	/**
	 * Sets the default values of the settings
	 * Called when creating a new session
	 * @param String $id session_id
	 */
	private function set_default_settings($id) {
		// node_thr
		$sql = "INSERT INTO sessions_settings (seed, setting_key, setting_value) " .
				"VALUES ( '" . $id . "', 'node_thr', '1000' )";
		$q = $this->query($sql);
	}

	private function write_settings() {
		foreach ( $this->settings_labels as $label ) {
			if ( isset($this->settings[$label]) ) {
				$sql = "SELECT * FROM sessions_settings WHERE seed = '" . $this->id . "' AND setting_key = '" . $label . "'";
				$q = $this->query($sql);

				if ( $q->size() != 0 ) {
					$sql = "UPDATE sessions_settings SET " .
					"setting_value = '" . $this->settings[$label] . "' " .
					"WHERE seed = '" . $this->id . "' AND setting_key = '" . $label . "'";
					$q = $this->query($sql);
				} else {
					$sql = "INSERT INTO sessions_settings (seed, setting_key, setting_value) " .
					"VALUES ( '" . $this->id . "', '" . $label . "', '" . $this->settings[$label] . "' )";
					$q = $this->query($sql);
				}
			}
		}
	}

}
?>
