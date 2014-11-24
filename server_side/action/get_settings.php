<?php
/**
 * Loads session settings.
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

	$settings = $s->get('settings');

	// Answer call
	die('{"err":0, "sif_sample_col":"' . $settings['sif_sample_col'] . '", "node_thr":"' . $settings['node_thr'] . '", "default_layout":"' . $settings['default_layout'] . '"}');

} else {
	die('{"err":3}');
}

?>
