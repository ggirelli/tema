<?php

// Requirements
require_once('functions.lib.php');
require_once('tea.db.class.php');

/**
* Class that manages TEMA users
* @author Gabriele Girelli <gabriele@filopoe.it>
* @since 0.3.0
*/
class TEMAuser extends TEAdb {

	// ATTRIBUTES
	
	/**
	 * Username
	 * @var String
	 */
	private $username;

	/**
	 * Email
	 * @var String
	 */
	private $email;

	/**
	 * Password
	 * @var String
	 */
	private $password;

	/**
	 * Confirmation token
	 * @var String
	 */
	private $confirm_token;

	/**
	 * When the token was sent
	 * @var String
	 */
	private $token_when;

	/**
	 * Whether the user is confirmed
	 * @var Boolean
	 */
	private $confirmed;

	/**
	 * Whether the provided credentials are acceptable
	 * @var Boolean
	 */
	private $checked;

	/**
	 * Whether the user exists
	 * @var Boolean
	 */
	private $exists;

	/**
	 * Whether the user can be considered log
	 * @var Boolean
	 */
	private $logged;

	/**
	 * Message codes (integers)
	 * 1:	username not correctly formatted
	 * 2:	email not correctly formatted
	 * 3:	password not correctly formatted
	 * 4:	username already in use
	 * 5:	email already in use
	 * 6:	provided credentials match existing user
	 * 7:	wrong password provided
	 * @var Array
	 */
	private $msg;

	// static CONSTANTS

	public $modes = TUModes();

	// public FUNCTIONS

	/**
	 * Connects with the server.
	 * @param String $host    MySQL host address
	 * @param String $user    MySQL user
	 * @param String $pwd     MySQL password
	 * @param String $db_name Database name
	 * @param String $tema_username 	Username for TEMA user
	 * @param String $tema_email 		Email for TEMA user
	 * @param String $tema_password 	Password for TEMA user
	 * @param   [varname] [description]
	 */
	public function __construct(
		$host, $user, $pwd, $db_name,
		$tema_username, $tema_email, $tema_password,
		$mode
	) {
		parent::__construct($host, $user, $pwd, $db_name);
		$this->init($tema_username, $tema_email, $tema_password);

		switch($mode) {
			case $modes::SIGNIN: {
				// Signing in
				break;
			}
			case $modes::SIGNUP: {
				// Signing up
				break;
			}
			default: {
				// WRONG!!!
			}
		}
	}

	/**
	 * Determines whether a certain username is already in use
	 * @param  String $user username
	 * @return Boolean
	 */
	public function username_exists($user) {
		$user = $this->escape_string($user);
		$sql = "SELECT id FROM sessions_user WHERE nickname = '" . $user . "'";
		$r = $this->query($sql);
		if( 1 >= $r->size() ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Determines whether a certain email is already in use
	 * @param  String $email email
	 * @return Boolean
	 */
	public function email_exists($email) {
		$email = $this->escape_string($email);
		$sql = "SELECT id FROM sessions_user WHERE email = '" . $email . "'";
		$r = $this->query($sql);
		if( 1 >= $r->size() ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Checks if the provided string is a possible user
	 * @param  String $user user candidate
	 * @return Boolean
	 */
	public function username_check($user) {
		if( count($user) < 4 ) return false;
		return true;
	}

	/**
	 * Checks if the provided string is a possible email
	 * @param  String $email email candidate
	 * @return Boolean
	 */
	public function email_check($email) {
		if( 1 == preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/', $email) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Checks if the provided string is a possible password
	 * @param  String $password password candidate
	 * @return Boolean
	 */
	public function password_check($password) {
		if( count($password) < 8 ) return false;
		if( 1 != preg_match($password, "/^.*[a-z].*$/") ) return false;
		if( 1 != preg_match($password, "/^.*[A-Z].*$/") ) return false;
		if( 1 != preg_match($password, "/^.*[0-9].*$/") ) return false;
		return true;
	}

	// private FUNCTIONS
	
	/**
	 * Initializes the user inside the class
	 * @param  String $username
	 * @param  String $email
	 * @param  String $password
	 * @return NULL           Initializes the class
	 */
	private function init($username, $email, $password) {
		// Default attributes
		$this->username = $username;
		$this->password = $this->encrypt($password);
		$this->email = $email;
		$this->confirm_token = NULL;
		$this->confirmed = FALSE;
		$this->checked = TRUE;
		$this->exists = FALSE;
		$this->logged = FALSE;
		$this->msg = new Array();

		// Test provided credentials for correct format
		if( $this->username_check($this->username) ) {
			$this->checked = FALSE;
			$this->msg[] = 1;
		}
		if( $this->email_check($this->email) ) {
			$this->checked = FALSE;
			$this->msg[] = 2;
		}
		if( $this->password_check($this->password) ) {
			$this->checked = FALSE;
			$this->msg[] = 3;
		}

		// If the provide credentials are correctly formatted
		if( $this->checked ) {
			// Test provided credential for match in database
			if( $this->username_exists($this->username) ) {
				$this->exists = 'username';
				$this->msg[] = 4;
			}
			if( $this->email_exists($this->email) ) {
				$this->exists = 'email';
				$this->msg[] = 5;
			}

			// Verify if a match was found
			switch($this->exists) {
				case 'username': {
					// Retrieve user from server and load it inside the class
					$this->load('nickname', $this->username);
					$this->exists = TRUE;
					break;
				}
				case 'email': {
					// Retrieve user from server and load it inside the class
					$this->load('email', $this->email);
					$this->exists = TRUE;
					break;
				}
			}

			// If a match was found and loaded, test the password
			if( $this->exists ) {
				if ( $this->isPassword() ) {
					$this->msg[] = 6;
					$this->logged = TRUE;
				} else {
					$this->msg[] = 7;
				}
			}
		}
	}
	
	/**
	 * Loads a user in the current class instance
	 * @param  String $field label of the field used for user matching
	 * @param  String $value value of the field used for user matching
	 * @return Boolean        Whether a user has been loaded or not
	 */
	private function load($field, $value) {
		$field = $this->escape_string($field);
		$value = $this->escape_string($value);

		$sql = "SELECT * FROM sessions_users WHERE $field = '$value'";
		$r = $this->query($sql);
		if( $r->isError() ) return FALSE;
		
		$row = $r->fetch();

		$this->username = $row['nickname'];
		$this->password = NULL;
		$this->email = $row['email'];
		$this->confirm_token = $row['confirm_token'];
		$this->token_when = $row['token_when'];
		$this->confirmed = $row['confirmed'];

		return TRUE;
	}

	/**
	 * Tests in-class stored password against the one stored in the database
	 * @param  String $field label of the field used for user matching
	 * @param  String $value value of the field used for user matching
	 * @return Boolean           Whether the passwords coincide
	 */
	private function isPassword($field, $value) {
		$field = $this->escape_string($field);
		$value = $this->escape_string($value);
		$password = $this->escape_string($password);

		$sql = "SELECT password FROM sessions_users WHERE $field = '$value'";
		$r = $this->query($sql);
		if( $r->isError() ) return FALSE;

		$row = $r->fetch();
		if( $row['password'] == $this->password ) return TRUE;
		return FALSE;
	}

	/**
	 * Perform a one-way hashing of provided string
	 * @param  String $s
	 * @return String    Hashed version of $s
	 */
	private function encrypt($s) {
		return(md5('TEMA' . sha1($s . md5('TEMA')) . sha1('TEMA')));
	}
}

/**
* Class that containts TEMAusers class initialization modes as constants
* @author Gabriele Girelli <gabriele@filopoe.it>
* @since 0.3.0
 */
class TUModes {
	public CONST SIGNIN = 'tema_sign_in_action';
	public CONST SIGNUP = 'tema_sign_up_action';

	public function __construct() {}
}

?>