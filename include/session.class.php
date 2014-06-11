<?php

require_once(dirname(dirname(__FILE__)) . '/SOGI-settings.php');
require_once('functions.lib.php');

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
	 * Front-end uri to the session interface
	 * @var String
	 */
	private $interface_uri;

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

	/**
	 * List of filenames that cannot be uploaded
	 * @var String
	 */
	private $banned_fnames;

	/**
	 * Name of the current graph
	 * @var String
	 */
	private $graph;

	/*-----------*/
	/* FUNCTIONS */
	/*-----------*/

	/**
	 * Loads a SOGIsession or creates a new one.
	 */
	public function __construct($ban, $id = null) {
		$this->banned_fnames = $ban;
		if(is_null($id)) {
			$this->init();
		} else {
			$this->load($id);
		}
	}

	/**
	 * Initialize a new SOGIsession in the current instance, if empty, or returns a new SOGIsession class instance.
	 * @return SOGIsession     A new SOGIsession instance if the current one is not empty.
	 */
	public function init() {
		if(-1 == $this->id) {
			// Initialize a new session into the current instance
			
			# Prepare ID
			$this->id = time() . random_string(10);
			while(SOGIsession::is($this->id)) {
				$this->id = time() . random_string(10);
			}

			# Store info
			$this->folder_path = SESS_PATH . $this->id . '/';
			$this->interface_uri = ROOT_URI . 's/' . $this->id;
			$this->running = 0;
			$this->last_query = 'init';
			$this->last_query_when = time();
			$this->graph = 0;

			# Make directory
			mkdir(SESS_PATH . $this->id);
			# Write CONFIG in directory
			$this->writeSession();

			file_put_contents(SESS_PATH . $this->id . '/CONSOLE', '');
		} else {
			return new SOGIsession();
		}
	}
	
	/**
	 * Load a SOGIsession in the current instance, if empty, or returns a new SOGIsession class instance.
	 * @param  int $id ID of the SOGIsession to load
	 * @return SOGIsession     A new SOGIsession instance if the current one is not empty.
	 */
	public function load($id) {
		if(-1 == $this->id) {
			// Load into the current instance
			$this->readSession($id);
		} else {
			return new SOGIsession($id);
		}
	}

	/**
	 * Retrieve the value of a parameter, in the current session.
	 * @param  String $attr Attribute name
	 * @return mix       The value of the attribute
	 */
	public function get($attr) {
		// Return attribute value
		switch($attr) {
			case 'id': {
				return $this->id;
				break;
			}
			case 'uri': {
				return $this->interface_uri;
				break;
			}
			case 'path': {
				return $this->folder_path;
				break;
			}
			case 'running': {
				return $this->running;
				break;
			}
			case 'last': {
				return $this->last_query;
				break;
			}
			case 'when': {
				return $this->last_query_when;
				break;
			}
			case 'graph': {
				return $this->graph;
				break;
			}
		}
		return NULL;
	}

	/**
	 * Sets the value of certain parameters, in the current session
	 * @param String $attr Attribute name
	 * @param mix $val  The value of the attribute
	 * @return  none
	 */
	public function set($attr, $val) {
		switch($attr) {
			case 'running': {
				$this->running = $val;
				$this->writeSession();
				break;
			}
			case 'last': {
				$this->last_query = $val;
				$this->writeSession();
				break;
			}
			case 'time': {
				$this->last_query_when = $val;
				$this->writeSession();
				break;
			}
			case 'graph': {
				$this->graph = $val;
				$this->writeSession();
				break;
			}
		}
	}

	/**
	 * Sets the value of some parameters, in the current session
	 * @param list $l {$attr:$val,...}
	 * @return  none
	 */
	public function multiset($l) {
		foreach($l as $attr => $val) {
			switch($attr) {
				case 'running': {
					$this->running = $val;
					break;
				}
				case 'last': {
					$this->last_query = $val;
					break;
				}
				case 'time': {
					$this->last_query_when = $val;
					break;
				}
				case 'graph': {
					$this->graph = $val;
					break;
				}
			}
		}
		$this->writeSession();
	}

	/**
	 * Retrieves the actual file list for the current session.
	 * @return array List of file names
	 */
	public function getCurrFileList() {
		$flist = array();
		foreach(scandir(SESS_PATH . $this->id) as $fname) {
			if(!in_array($fname, $this->banned_fnames)) {
				$flist[] = $fname;
			}
		}

		// Return the current list of file names
		return($flist);
	}

	/**
	 * Retrieves the actual graphml file list for the current session.
	 * @return array List of file names
	 */
	public function getGraphmlFileList() {
		$flist = array();
		foreach(glob(SESS_PATH . $this->id . '/*.graphml') as $fname) {
			$fname = basename($fname, '.graphml');
			if(!in_array($fname, $this->banned_fnames)) {
				$flist[] = $fname;
			}
		}

		// Return the current list of file names
		return($flist);
	}

	/**
	 * Retrieves the actual JSON file list for the current session.
	 * @return array List of file names
	 */
	public function getJSONFileList() {
		$flist = array();
		foreach(glob(SESS_PATH . $this->id . '/*.json') as $fname) {
			$fname = basename($fname, '.json');
			if(!in_array($fname, $this->banned_fnames)) {
				$flist[] = $fname;
			}
		}

		// Return the current list of file names
		return($flist);
	}

	/**
	 * Retrieves the actual list of file to be converted into JSON, for the current session.
	 * @return array List of file names
	 */
	public function getToConvertFileList() {
		$glist = $this->getGraphmlFileList();
		$jlist = $this->getJSONFileList();

		$uncommon = array();

		foreach($glist as $gfile) {
			if(!in_array($gfile, $jlist)) {
				$uncommon[] = $gfile;
			}
		}
		
		return($uncommon);
	}

	// -------
	// PRIVATE
	// -------

	/**
	 * Writes a config file with all the session params.
	 * @return Boolean T if success
	 */
	private function writeSession() {
		$data = '';
		$data .= "ID\t$this->id\n";
		$data .= "PATH\t$this->folder_path\n";
		$data .= "URI\t$this->interface_uri\n";
		$data .= "RUNNING\t$this->running\n";
		$data .= "LAST\t$this->last_query\n";
		$data .= "TIME\t$this->last_query_when\n";
		$data .= "GRAPH\t$this->graph";
		file_put_contents($this->folder_path . 'CONFIG', $data);
	}

	/**
	 * Reads a session from its config file
	 * @param  int $id The ID of the session to read
	 * @return array     Session attributes
	 */
	private function readSession($id) {
		$f = fopen(SESS_PATH . $id . '/CONFIG', 'r');
		while(($row = fgets($f)) !== FALSE) {
			$arow = split("\t", trim($row));
			switch($arow[0]) {
				case 'ID': {
					$this->id = $arow[1];
					break;
				}
				case 'PATH': {
					$this->folder_path = $arow[1];
					break;
				}
				case 'URI': {
					$this->interface_uri = $arow[1];
					break;
				}
				case 'RUNNING': {
					$this->running = $arow[1];
					break;
				}
				case 'LAST': {
					$this->last_query = $arow[1];
					break;
				}
				case 'TIME': {
					$this->last_query_when = $arow[1];
					break;
				}
				case 'GRAPH': {
					$this->graph = $arow[1];
					break;
				}
			}
		}
		fclose($f);
		return TRUE;
	}

	// ------
	// STATIC
	// ------

	/**
	 * Determines if a session with the given id exists
	 * @param  int  $id session id
	 * @return boolean     T if it exists
	 */
	public static function is($id) {
		if(in_array($id, scandir(SESS_PATH))) return TRUE;
		return FALSE;
	}

	public static function exec($FILENAME_BAN, $id, $name, $query) {
		$ss = new SOGIsession($FILENAME_BAN, $id);
		if($ss->get('running') == 0) {
			$ss->multiset(array(
				'running' => 1,
				'last' => $name,
				'time' => time()
			));

			exec($query, $res);
			#print_r($res);

			$ss->set('running', 0);
			return(TRUE);
		} else {
			return FALSE;
		}
	}

	public static function execReturn($FILENAME_BAN, $id, $name, $query) {
		$ss = new SOGIsession($FILENAME_BAN, $id);
		if($ss->get('running') == 0) {
			$ss->multiset(array(
				'running' => 1,
				'last' => $name,
				'time' => time()
			));

			exec($query, $res);

			$ss->set('running', 0);
			return($res);
		} else {
			return 'ERROR';
		}
	}

}

?>