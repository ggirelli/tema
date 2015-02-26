<?php
/**
 * Maps GOs.
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

	$goa = SPATH . '/' . $data->id . '/settings/goa.dat';
	$gob = SPATH . '/' . $data->id . '/settings/gob.dat';

	if ( file_exists($goa) and file_exists($gob) ) {
		$mgmt = SPATH . '/' . $data->id . '/settings/go_mgmt.Rdata';
		if ( file_exists($mgmt) ) unlink($mgmt);

		$q = 'cd ' . SCRIPATH . '; ./GOsMapper.R ' . $s->get('id');
		$r = $s->exec_return('convert', $q);

		die('{"err":0}');
	}

	die('{"err":4');
} else {
	die('{"err":3}');
}

?>
