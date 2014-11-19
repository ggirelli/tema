<?php
/**
 * Loads session network.
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
	$network_status = array_values($network_list)[$data->network_id]->status;

	if ( 1 == $network_status ) {
		$network = file_get_contents($s->get('folder_path') . '/' . $network_name . '.json');
		$data = file_get_contents($s->get('folder_path') . '/' . $network_name . '.dat');
		die('{"err":0,"network":' .  $network . ', "data":' . $data . '}');
	} else {
		die('{"err":4}');
	}

} else {
	die('{"err":3}');
}

?>
