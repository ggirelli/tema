<?php
/**
 * Downloads session files.
 * @since 0.3.0
 */

require_once('settings.php');
require_once('include/tema.session.class.php');

if ( isset($_GET['f']) and isset($_GET['s']) ) {
	$s = new TEMAsession(HOST, USER, PWD, DB_NAME);
	if ( $s->exists($_GET['s']) ) {

		$file = SPATH . '/' . $_GET['s'] . '/' . $_GET['f'];
		if ( file_exists($file) ) {

			$quoted = sprintf('"%s"', addcslashes(basename($file), '"\\'));
			$size   = filesize($file);

			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . $quoted); 
			header('Content-Transfer-Encoding: binary');
			header('Connection: Keep-Alive');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . $size);

			echo file_get_contents($file);
			die();

		} else {
			echo 1;
		}
	} else {
		echo 2;
	}
}

die('ERROR');

?>