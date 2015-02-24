<?php
/**
 * Lists TEMA sessions
 * @author Gabriele Girelli <gabriele@filopoe.it>
 * @since  0.2.0
 */

// Requirements
require_once(dirname(dirname(__FILE__)) . '/include/tema.session.class.php');
require_once(dirname(dirname(__FILE__)) . '/include/tema.user.class.php');

$user = new TEMAuser(
	HOST, USER, PWD, DB_NAME, TUModes::SIMPLE,
	$data->usr, NULL, NULL, NULL
);

if ( 'owned' == $data->type ) {
	$list = $user->list_owned_sessions();

	$json = '{"list":[';
	for ($i = 0; $i < count($list); $i++) {
		$session = $list[$i];
		$json .= '{"seed":"' . $session['seed'] .'", ' .
			'"title":"' . $session['title'] .'", ' .
			'"privacy":"' . $session['privacy'] .'", ' .
			'"password":"' . $session['password'] .'"}';
		if ( $i != count($list)-1 ) $json .= ",";
	}
	$json .= '], "err":0}';

	die($json);
}

die('{"err":3, "list":[]}');

?>