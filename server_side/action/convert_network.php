<?php
/**
 * Converts a network.
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
	$network_status = array_values($network_list)[$data->network_id]->status;

	if ( 0 == $network_status ) {
		// Convert the network
		$q = 'cd ' . SCRIPATH . '; ./convertToJSON.R ' . $s->get('id') . ' ' . $network_name . ' ' . $data->layout;
		$r = $s->exec_return('convert', $q);
		
		// Update network status
		$network_list = $s->get('network_list');
		$network_name = array_keys($network_list)[$data->network_id];
		$network_data = array_values($network_list)[$data->network_id]->data;
		$network_status = array_values($network_list)[$data->network_id]->status;
	}
	
	// Answer call
	echo '{"err":0, "name":"' . $network_name . '", "status": "' . $network_status . '", "data":' . $network_data . '}';

} else {
	echo '{"err":3}';
}

?>
