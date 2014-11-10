<?php
/**
 * Loads session settings.
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

	$sif_sample_col = $s->get('sif_sample_col');

	// Answer call
	die('{"err":0, "sif_sample_col":[' . $sif_sample_col . ']}');

} else {
	die('{"err":3}');
}

?>
