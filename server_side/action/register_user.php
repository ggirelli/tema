<?php
/**
 * Attempts to create a new TEMA user.
 * @author Gabriele Girelli <gabriele@filopoe.it>
 * @since  0.3.0
 */

// Requirements
require_once(RPATH . '/include/tema.user.class.php');

$user = new TEMAuser(
	HOST, USER, PWD, DB_NAME,
	$data->user, $data->email, $data->password,
	TUModes::SIGNUP
);

$r = $user->get_msg();

// Credentials not correctly formatted
if( in_array(1, $r) || in_array(2, $r) || in_array(3, $r))
	die('{"err":3}');

// Username already in use
if( in_array(4, $r) )
	die('{"err":4}');
// Email already in use
if( in_array(5, $r) )
	die('{"err":5}');

if($user->isError()) {
	// An error occurred while writing user into the DB
	die('{"err":6}');
}

?>