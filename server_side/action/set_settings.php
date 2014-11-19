<?php
/**
 * Writes session settings.
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

	$new_settings = $data->data;
	$parsed_settings = array(
		"sif_sample_col" => (String)$new_settings->sif_sample_col,
		"node_thr" => (String)$new_settings->node_thr
	);
	$s->apply_settings($parsed_settings);

	// Answer call
	die('{"err":0}');

} else {
	die('{"err":3}');
}

?>
