<?php
/**
 * Contains network.
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
	
	$q = 'cd ' . SCRIPATH . '; ./getNeighborhood.R ' . $s->get('id') . ' ' . $data->name . ' ' . $data->node . ' ' . $s->get('settings')['node_thr'] . ' ' . $data->mode;
	$r = $s->exec_return('convert', $q);

	$target = SPATH . '/' . $s->get('id') . '/tmp_r_output';
	if ( file_exists($target) ) {
		$contents = file_get_contents($target);
		unlink($target);
		die('{"err":0,"neigh":' . $contents . '}');
	} else {
		die('{"err":4}');
	}

} else {
	die('{"err":3}');
}

?>
