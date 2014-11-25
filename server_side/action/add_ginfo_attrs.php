<?php
/**
 * Add a node GO attribute to the network visualization.
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

	// Write JSON
	$f = SPATH . '/' . $data->id . '/' . $data->name . '.json';
	file_put_contents($f, $data->network);

	if ( file_exists( SPATH . '/' . $data->id . '/settings/go_mgmt.Rdata' ) ) {
		// Custom GO mgmt
		$data->go_type = 'custom';
	} else {
		// Default GO mgmt
		$data->go_type = 'default';
	}

	$q = 'cd ' . SCRIPATH . '; ./addGinfoAttributesToNetwork.R ' . $s->get('id') . ' ' . $data->name . ' ' . $data->go_type . ' ' . $data->attr_hugo;
	$r = $s->exec_return('convert', $q);
	//print_r($r);

	// Answer call
	echo '{"err":0, "net": ' . file_get_contents($f) . '}';
	unlink($f);

} else {
	echo '{"err":3}';
}

?>
