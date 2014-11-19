<?php
/**
 * Uploads and converts the SIF.
 * @author Gabriele Girelli <gabriele@filopoe.it>
 * @since  0.2.0
 */

// Requirements
require_once(dirname(dirname(__FILE__)) . '/include/tea.session.class.php');

// Connect to database
$s = new TEAsession(HOST, USER, PWD, DB_NAME);

// Convert $_POST to $data
$data->id = $_POST['id'];

if ( $s->exists($data->id) ) {
	// Load session
	$s->init($data->id);

	$newname = $_FILES['file']['name']; 
	$target = SPATH . '/' . $data->id . '/sif.dat';

	move_uploaded_file( $_FILES['file']['tmp_name'], $target);

	$q = 'cd ' . SCRIPATH . '; ./parseSIF.R ' . $s->get('id');
	$r = $s->exec_return('parseSIF', $q);

	// File uploaded correcty
	die('{"err":0}');

} else {
	echo '{"err":3}';
}

?>
