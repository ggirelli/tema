<?php
/**
 * Loads session settings.
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

	$settings = $s->get('settings');

	$shared_with = $s->shared_with();
	$shared_string = '[';
	for ( $i = 0; $i < count($shared_with); $i++ ) {
		$shared_string .= '"' . $shared_with[$i] . '"';
		if ( $i != count($shared_with)-1 ) $shared_string .= ',';
	}
	$shared_string .= ']';

	// Answer call
	die('{"err":0, "sif_sample_col":"' . $settings['sif_sample_col'] . '", ' .
		'"node_thr":"' . $settings['node_thr'] . '", ' .
		'"default_layout":"' . $settings['default_layout'] . '",' .
		'"shared_with":' . $shared_string .
		'}');

} else {
	die('{"err":3}');
}

?>
