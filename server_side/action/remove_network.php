<?php
/**
 * Rename network.
 * @author Gabriele Girelli <gabriele@filopoe.it>
 * @since  0.2.0
 */

// Requirements
require_once(dirname(dirname(__FILE__)) . '/include/tea.session.class.php');

// Connect to database
$s = new TEAsession(HOST, USER, PWD, DB_NAME);

if ( $s->exists($data->id) ) {
	// Load session
	$s->init($data->id);

	// Read network
	$network_list = $s->get('network_list');
	$network_name = array_keys($network_list)[$data->network_id];

	$f = SPATH . '/' . $data->id . '/' . $network_name;
	if ( file_exists($f . '.graphml') ) unlink($f . '.graphml');
	if ( file_exists($f . '.dat') ) unlink($f . '.dat');
	if ( file_exists($f . '.json') ) unlink($f . '.json');

} else {
	die('{"err":3}');
}

?>
