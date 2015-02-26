<?php
/**
 * Add a node index attribute to the network visualization.
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

	// Write JSON
	$f = SPATH . '/' . $data->id . '/' . $data->name . '.json';
	file_put_contents($f, $data->network);

	// Convert the network
	$q = 'cd ' . SCRIPATH . '; ./addIndexAttributeToNetwork.R ' . $s->get('id') . ' ' . $data->name . ' ' . $data->attr_name . ' ' . $data->attr_index;
	$r = $s->exec_return('convert', $q);

	// Answer call
	echo '{"err":0, "net": ' . file_get_contents($f) . '}';
	unlink($f);

} else {
	echo '{"err":3}';
}

?>
