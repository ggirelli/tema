<?php
/**
 * Manages TEMA session sharing
 * @author Gabriele Girelli <gabriele@filopoe.it>
 * @since  0.3.0
 */

// Requirements
require_once(dirname(dirname(__FILE__)) . '/include/tema.session.class.php');
require_once(dirname(dirname(__FILE__)) . '/include/tema.user.class.php');

$s = new TEMAsession(HOST, USER, PWD, DB_NAME);
$s->init($data->session_id);

function make_shared_string ($shared_with) {
	$shared_string = '[';
	for ( $i = 0; $i < count($shared_with); $i++ ) {
		$shared_string .= '"' . $shared_with[$i] . '"';
		if ( $i != count($shared_with)-1 ) $shared_string .= ',';
	}
	$shared_string .= ']';
	return $shared_string;
}
$shared_string = make_shared_string($s->shared_with());

if ( isset($data->usr) and 0 != @strlen($data->usr) ) {

	$user = new TEMAuser(
		HOST, USER, PWD, DB_NAME, TUModes::SIMPLE,
		$data->usr, NULL, NULL, NULL
	);

	// Unknown user
	if ( !$user->username_exists($data->usr) ) die('{"err":5, "list":' . $shared_string . '}');

	switch($data->type) {
		case 'add': {
			$r = $s->share_with($data->usr);
			$shared_string = make_shared_string($s->shared_with());
			if ( is_null($r) ) {
				// Unkown user or owner
				die('{"err":6, "list":' . $shared_string . '}');
			} else if ( $r ) {
				die('{"err":0, "list":' . $shared_string . '}');
			} else {
				// An error occurred
				die('{"err":7, "list":' . $shared_string . '}');
			}
			break;
		}
		case 'rm': {
			$r = $s->rm_share_with($data->usr);
			$shared_string = make_shared_string($s->shared_with());
			if ( is_null($r) ) {
				// Unkown user
				die('{"err":8, "list":' . $shared_string . '}');
			} else if ( $r ) {
				die('{"err":0, "list":' . $shared_string . '}');
			} else {
				// An error occurred
				die('{"err":9, "list":' . $shared_string . '}');
			}
			break;
		}
		default: {
			// undefined action type
			die('{"err":4, "list":' . $shared_string . '}');
		}
	}
} else {
	// user not provided
	die('{"err":3, "list":' . $shared_string . '}');
}

?>