<?php

// Requirements
require_once('functions.lib.php');
require_once('tema.db.class.php');
require_once(dirname(__FILE__) . '/PHPMailer/class.phpmailer.php');

/**
* Class that manages TEMA users
* @author Gabriele Girelli <gabriele@filopoe.it>
* @since 0.3.0
*/
class TEMAuser extends TEMAdb {

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
	 * 8:	user not confirmed
	 * 9:	non-existent confirmation token
	 * 10:	confirmation token already used
	 * 11:	an error occurred during confirmation
	 * 12:	user confirmed
	 * 13:	an error occur while sending the confirmation email
	 * 14:	confirmation email sent
	 * @var Array
	 */
	private $msg;

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
		$host, $user, $pwd, $db_name, $mode,
		$tema_username=NULL, $tema_email=NULL, $tema_password=NULL,
		$tema_token=NULL
	) {
		if ( -1 === $tema_email ) $tema_email = NULL;

		// Username AND/OR  Email must be provided
		if( is_null($tema_username) && is_null($tema_email) ) return NULL;
		// If SIGNUP mode, BOTH username AND email must be provided
		if( $mode == TUModes::SIGNUP ) {
			if( is_null($tema_username) || is_null($tema_email) ) return NULL;
		}

		parent::__construct($host, $user, $pwd, $db_name);
		$this->init($tema_username, $tema_email, $tema_password, $tema_token);

		switch($mode) {
			case TUModes::SIGNUP: {
				$this->signUp();
				break;
			}
			case TUModes::SIGNIN: {
				if ( !$this->logged ) {
					$this->init($tema_email, $tema_username, $tema_password, $tema_token);
				}
				break;
			}
			case TUModes::CONFRM: {
				$this->confirm();
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
		$sql = "SELECT id FROM sessions_users WHERE nickname = '" . $user . "'";
		$r = $this->query($sql);
		if( 1 <= $r->size() ) {
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
		$sql = "SELECT id FROM sessions_users WHERE email = '" . $email . "'";
		$r = $this->query($sql);
		if( 1 <= $r->size() ) {
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
		if( strlen($user) < 4 ) return false;
		return true;
	}

	/**
	 * Checks if the provided string is a possible email
	 * @param  String $email email candidate
	 * @return Boolean
	 */
	public function email_check($email) {
		if( 1 == preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/', strtoupper($email)) ) {
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
		if( strlen($password) < 8 ) return false;
		if( 1 != preg_match('/^.*[a-z]+.*$/', $password) ) return false;
		if( 1 != preg_match('/^.*[A-Z]+.*$/', $password) ) return false;
		if( 1 != preg_match('/^.*[0-9]+.*$/', $password) ) return false;
		return true;
	}

	/**
	 * @return Array with the code of the messages
	 */
	public function get_msg() { return $this->msg; }

	/**
	 * @return String The username
	 */
	public function get_username() { return $this->username; }

	/**
	 * @return Array A list of sessions owned by the user
	 */
	public function list_owned_sessions() {
		$user_id = $this->get_id($this->username);

		// Retrieve owned sessions
		$sql = "SELECT seed, title, privacy, password FROM sessions WHERE owner='$user_id'";
		$r = $this->query($sql);

		// Hide password and prepare multi-array
		$l = array();
		while($row = $r->fetch()) {
			if ( '' === $row['password'] ) {
				$row['password'] = 0;
			} else {
				$row['password'] = 1;
			}
			$l[] = $row;
		}

		return($l);
	}

	/**
	 * @return Array A list of sessions shared with the user
	 */
	public function list_shared_sessions() {
		$user_id = $this->get_id($this->username);

		// Retrieve shared sessions
		$sql = "SELECT sh.seed, se.title, se.privacy, se.password, su.nickname FROM sessions_shared AS sh " .
			"LEFT JOIN sessions AS se ON sh.seed=se.seed " .
			"LEFT JOIN sessions_users AS su ON se.owner=su.id " .
			"WHERE sh.user_id=$user_id";
		$r = $this->query($sql);

		// Hide password and prepare multi-array
		$l = array();
		while($row = $r->fetch()) {
			if ( '' === $row['password'] ) {
				$row['password'] = 0;
			} else {
				$row['password'] = 1;
			}
			$l[] = $row;
		}

		return($l);
	}

	/**
	 * @return Array A list of sessions visited by the user
	 */
	public function list_history_sessions() {
		$user_id = $this->get_id($this->username);

		// Retrieve history
		$sql = "SELECT s.seed, s.title, s.privacy, s.password, h.date FROM `sessions_history` AS h " .
			"LEFT JOIN `sessions` AS s ON h.seed=s.seed WHERE h.user_id=$user_id";
		$r = $this->query($sql);

		// Hide password and prepare multi-array
		$l = array();
		while($row = $r->fetch()) {
			if ( '' === $row['password'] ) {
				$row['password'] = 0;
			} else {
				$row['password'] = 1;
			}
			$l[] = $row;
		}

		return(array_reverse($l));
	}

	/**
	 * Updates the user's session history
	 * @param  String $seed session seed
	 * @return Boolean 	whether the operations succeded
	 */
	public function update_history($seed) {
		$user_id = $this->get_id($this->username);
		$seed = $this->escape_string($seed);

		// Update session history
		$sql = "INSERT INTO sessions_history " .
			"(user_id, seed)" .
			"VALUES ('$user_id', '$seed')";
		$r = $this->query($sql);

		if ( $this->isError() ) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	// private FUNCTIONS
	
	/**
	 * Initializes the user inside the class
	 * @param  String $username (toupper)
	 * @param  String $email
	 * @param  String $password
	 * @param  String $token
	 * @return NULL           Initializes the class
	 */
	private function init($username, $email, $password, $token) {
		// Default attributes
		$this->username = $username;
		if ( !is_null($this->username) ) $this->username = strtoupper($this->username);
		$this->password = $password;
		$this->email = $email;
		if ( !is_null($this->email) ) $this->email = strtoupper($this->email);
		$this->confirm_token = $token;
		$this->confirmed = FALSE;
		$this->checked = TRUE;
		$this->exists = FALSE;
		$this->logged = FALSE;
		$this->msg = array();

		// Test provided credentials for correct format
		if( !is_null($this->username) ) {
			if( !$this->username_check($this->username) ) {
				$this->checked = FALSE;
				$this->msg[] = 1;
			}
		}
		if( !is_null($this->email) ) {
			if( !$this->email_check($this->email) ) {
				$this->checked = FALSE;
				$this->msg[] = 2;
			}
		}
		if( !$this->password_check($this->password) ) {
			$this->checked = FALSE;
			$this->msg[] = 3;
		} else {
			$this->password = $this->encrypt($password);
		}
		
		// If the provide credentials are correctly formatted
		if( $this->checked ) {
			// Test provided credential for match in database
			if( !is_null($this->username) ) {
				if( $this->username_exists($this->username) ) {
					$this->exists = 'username';
					$this->msg[] = 4;
				}
			}
			if( !is_null($this->email) ) {
				if( $this->email_exists($this->email) ) {
					$this->exists = 'email';
					$this->msg[] = 5;
				}
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
				if ( $this->confirmed ) {
					if ( $this->isPassword('nickname', $this->username) ) {
						$this->msg[] = 6;
						$this->logged = TRUE;
					} else {
						$this->msg[] = 7;
					}
				} else {
					$this->msg[] = 8;
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
		$this->email = $row['email'];
		$this->confirm_token = $row['confirm_token'];
		$this->token_when = $row['token_when'];
		if( 1 == $row['confirmed'] ) {
			$this->confirmed = TRUE;
		} else {
			$this->confirmed = FALSE;
		}

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

		$sql = "SELECT password FROM sessions_users WHERE $field = '$value'";
		$r = $this->query($sql);
		if( $r->isError() ) return FALSE;

		$row = $r->fetch();
		if( $row['password'] == $this->password ) return TRUE;
		return FALSE;
	}

	/**
	 * Determines whether a certain token is already in use
	 * @param  String $token token
	 * @return Boolean
	 */
	private function token_exists($token) {
		$token = $this->escape_string($token);
		$sql = "SELECT id FROM sessions_users WHERE confirm_token = '$token'";
		$r = $this->query($sql);
		if( 1 <= $r->size() ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Registers the user in the database
	 * @return NULL
	 */
	private function signUp() {
		$nickname = $this->escape_string($this->username);
		$email = $this->escape_string($this->email);
		$password = $this->password;
		$confirm_token = random_string(10) . sha1(time());
		while($this->token_exists($confirm_token)) {
			$confirm_token = random_string(10) . sha1(time());
		}

		// Insert user into DB
		$sql = "INSERT INTO sessions_users " .
			"(nickname, email, password, confirm_token, token_when, confirmed)" .
			" VALUES ('$nickname', '$email', '$password', '$confirm_token', NOW(), 0)";
		$r = $this->query($sql, $verbose=FALSE);

		if( !$this->isError() ) {
			// Send email for confirmation
			$mail = new PHPMailer;

			if( !is_null(SMTP_HOST) ) {
				$mail->isSMTP();
				$mail->Host = SMTP_HOST;
				$mail->Port = 25;
				$mail->SMTPAuth = false;
			}

			$mail->setFrom('info.tema@cibio.unitn.it', 'TEMA Bot');
			$mail->addAddress($this->email, $this->username);
			$mail->Subject = 'Welcome to TEMA!';

			$msgHTML = file_get_contents('static/email.confirmation.html');
			$msgHTML = str_replace('##USERNAME_TEMA##', $this->username, $msgHTML);
			$msgHTML = str_replace('##TEMA_URI##', RURI, $msgHTML);
			$msgHTML = str_replace('##TOKEN##', $confirm_token, $msgHTML);
			$mail->msgHTML($msgHTML);

			$mail->AltBody = "(this is an automatic email, do not write to this address)\n\n" .
				"Hello $this->username and welcome to TEMA!\n" .
				"To use the tools available in TEMA interface, " .
				"first you need to confirm your brand new account by clicking " .
				"on the following link: " . RURI . "/#/activation/$confirm_token\n" .
				"Cheers!\nTEMA staff";

			if (!$mail->send()) {
				$msg[] = 13;
			} else {
				$msg[] = 14;
			}
		}
	}

	/**
	 * Confirms a user in the database
	 * @return NULL
	 */
	private function confirm() {
		$confirm_token = $this->escape_string($this->confirm_token);

		// VERIFICATION OF TIME LIMIT IS NOT YET IMPLEMENTED
		
		// Look for the token
		$sql = "SELECT id FROM sessions_users WHERE confirm_token = '" . $confirm_token . "'";
		$r = $this->query($sql);

		if ( 1 <= $r->size() ) {
			$id = $r->fetch()['id'];
			// Confirm
			$sql = "SELECT confirmed FROM sessions_users WHERE id=$id";
			$r = $this->query($sql);
			$confirmed = $r->fetch()['confirmed'];

			if ( 0 == $confirmed ) {
				// Confirm user
				$sql = "UPDATE sessions_users SET confirmed=1 WHERE id=$id";
				$r = $this->query($sql);

				if ( $this->isError() )  {
					$this->msg[] = 11;
				} else {
					$confirmed = TRUE;
					$this->msg[] = 12;
				}
			} else {
				// Token already used
				$this->msg[] = 10;
			}
		} else {
			// Non-existent confirmation token
			$this->msg[] = 9;
		}
	}

	/**
	 * @param  String $usr username
	 * @return int		the id of the user in the DB
	 */
	private function get_id($usr) {
		// Retrieve user ID
		$sql = "SELECT id FROM sessions_users WHERE nickname='$usr'";
		$r = $this->query($sql);
		return $r->fetch()['id'];
	}
}

/**
* Class that containts TEMAusers class initialization modes as constants
* @author Gabriele Girelli <gabriele@filopoe.it>
* @since 0.3.0
 */
class TUModes {
	CONST SIGNIN = 'tema_sign_in_action';
	CONST SIGNUP = 'tema_sign_up_action';
	CONST CONFRM = 'tema_confirm_action';
	CONST SIMPLE = 'tema_simple_action';
}

?>