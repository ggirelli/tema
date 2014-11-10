<?php
/**
 * Converts a network.
 * @author Gabriele Girelli <gabriele@filopoe.it>
 * @since  0.2.0
 */

// Requirements
require_once(dirname(dirname(__FILE__)) . '/include/sogi.session.class.php');

// Connect to database
$s = new SOGIsession(HOST, USER, PWD, DB_NAME);

if ( $s->exists($data->id) ) {

	// Load session
	$s->init($data->id);

	// Read network
	$network_list = $s->get('network_list');
	$network_name = array_keys($network_list)[$data->network_id];
	$network_status = array_values($network_list)[$data->network_id];

	if ( 0 == $network_status ) {
		// Convert the network
		$r = $s->exec_return('convert', 'cd ' . SCRIPATH . '; ./convertToJSON.R ' . $s->get('id') . ' ' . $network_name);
		
		// Update network status
		$network_list = $s->get('network_list');
		$network_name = array_keys($network_list)[$data->network_id];
		$network_status = array_values($network_list)[$data->network_id];
	}

	// Answer call
	echo '{"err":0, "name":"' . $network_name . '", "status": "' . $network_status . '"}';

} else {
	echo '{"err":3}';
}

?>
