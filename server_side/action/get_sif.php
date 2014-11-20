<?php
/**
 * Loads the SIF.
 * @author Gabriele Girelli <gabriele@filopoe.it>
 * @since  0.2.0
 */

// Requirements
require_once(dirname(dirname(__FILE__)) . '/include/tea.session.class.php');

// Connect to database
$s = new TEAsession(HOST, USER, PWD, DB_NAME);

if ( $s->exists($data->id) ) {

	$f = SPATH . '/' . $data->id . '/settings/sif.json';
	if ( file_exists($f) ) {
		die('{"err":0,"sif":' . file_get_contents($f) . '}');
	} else {
		die('{"err":4}');
	}

} else {
	echo '{"err":3}';
}

?>
