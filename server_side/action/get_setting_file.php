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
	
	if ( 'sif' == $data->type ) {
		$f = SPATH . '/' . $data->id . '/settings/sif.json';
		if ( file_exists($f) ) {
			die('{"err":0,"sif":' . file_get_contents($f) . '}');
		} else {
			die('{"err":4}');
		}
	} else if ( in_array($data->type, Array('goa', 'gob')) ) {
		$f = SPATH . '/' . $data->id . '/settings/' . $data->type . '.dat';
		if ( file_exists($f) ) {
			die('{"err":0,"file":true}');
		} else {
			die('{"err":4}');
		}
	} else if ( 'go_mgmt' == $data->type ) {
		$f = SPATH . '/' . $data->id . '/settings/' . $data->type . '.Rdata';
		if ( file_exists($f) ) {
			die('{"err":0,"file":true}');
		} else {
			die('{"err":4}');
		}
	}

} else {
	echo '{"err":3}';
}

?>
