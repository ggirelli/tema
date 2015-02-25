<?php
/**
 * Rename network.
 * @author Gabriele Girelli <gabriele@filopoe.it>
 * @since  0.2.0
 */

// Requirements
require_once(dirname(dirname(__FILE__)) . '/include/tema.session.class.php');

// Connect to database
$s = new TEMAsession(HOST, USER, PWD, DB_NAME);

if ( $s->exists($data->id) ) {
	// Load session
	$s->init($data->id);

	// Read network
	$network_list = $s->get('network_list');
	$network_name = array_keys($network_list)[$data->network_id];

	$f = SPATH . '/' . $data->id . '/' . $network_name;
	$fn = SPATH . '/' . $data->id . '/' . $data->name;
	if ( file_exists($f . '.graphml') ) rename($f . '.graphml', $fn . '.graphml');
	if ( file_exists($f . '.dat') ) rename($f . '.dat', $fn . '.dat');
	if ( file_exists($f . '.json') ) rename($f . '.json', $fn . '.json');

} else {
	die('{"err":3}');
}

?>
