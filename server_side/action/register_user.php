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
	TUModes::SIGNIN
)

?>