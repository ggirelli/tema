<?php
/**
 * Attempts to create a new TEMA user.
 * @author Gabriele Girelli <gabriele@filopoe.it>
 * @since  0.3.0
 */

// Requirements
require_once(RPATH . '/include/tema.user.class.php');

$user = new TEMAuser(
	HOST, USER, PWD, DB_NAME, TUModes::CONFRM,
	0, 0, 0, $data->token
);

$r = $user->get_msg();

// Non-existent token
if ( in_array(9, $r) ) {
	die('{"err":3}');
}

// Token already used
if ( in_array(10, $r) ) {
	die('{"err":4}');
}

// An error occurred
if ( in_array(11, $r) ) {
	die('{"err":5}');
}

// User confirmed
if ( in_array(12, $r) ) {
	die('{"err":0}');
}

?>