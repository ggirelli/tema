<?php
/**
 * Attempts to sign in a TEMA user.
 * @author Gabriele Girelli <gabriele@filopoe.it>
 * @since  0.3.0
 */

// Requirements
require_once(RPATH . '/include/tema.user.class.php');

$user = new TEMAuser(
	HOST, USER, PWD, DB_NAME, TUModes::SIGNIN,
	$data->user, -1, $data->password
);

$r = $user->get_msg();

if ( is_null($r) )
	die('{"err":4}');

if ( in_array(6, $r) )
	die('{"err":0}');

die('{"err":3}');

?>