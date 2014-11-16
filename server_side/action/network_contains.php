<?php
/**
 * Contains network.
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

	$f = SPATH . '/' . $data->id . '/tmp_r_config.json';
	file_put_contents($f, json_encode($data));
	
	$q = 'cd ' . SCRIPATH . '; ./containsNetwork.R ' . $s->get('id') . ' tmp_r_config';
	$r = $s->exec_return('convert', $q);
	
	unlink($f);
	if ( $r[0] == 'TRUE' ) {
		die('{"err":0,"res":1}');
	} else {
		die('{"err":0,"res":0}');
	}

} else {
	die('{"err":3}');
}

?>
