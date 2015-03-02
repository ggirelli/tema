<?php
/**
 * Updates user's session history
 * @author Gabriele Girelli <gabriele@filopoe.it>
 * @since  0.3.0
 */

// Requirements
require_once(dirname(dirname(__FILE__)) . '/include/tema.session.class.php');
require_once(dirname(dirname(__FILE__)) . '/include/tema.user.class.php');

$user = new TEMAuser(
	HOST, USER, PWD, DB_NAME, TUModes::SIMPLE,
	$data->usr, NULL, NULL, NULL
);

$s = new TEMAsession(HOST, USER, PWD, DB_NAME);
if ( $s->exists($data->seed) ) {

	$r = $user->update_history($data->seed);
	if( $r ) {
		// Updated
		die('{"err":0}');
	} else {
		// Could not update
		die('{"err":4}');
	}

}

// Unknown seed
die('{"err":3, "list":[]}');

?>