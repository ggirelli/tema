<?php
/**
 * Save a network visualization.
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

	// Write JSON
	$f = SPATH . '/' . $data->id . '/' . $data->name . '.json';
	file_put_contents($f, $data->network);

	// Convert the network
	$q = 'cd ' . SCRIPATH . '; ./convertToGraphML.R ' . $s->get('id') . ' ' . $data->name;
	$r = $s->exec_return('convert', $q);

	// Answer call
	//echo '{"err":0}';

} else {
	echo '{"err":3}';
}

?>
