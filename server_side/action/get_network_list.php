<?php
/**
 * Loads session network list.
 * @author Gabriele Girelli <gabriele@filopoe.it>
 * @since  0.2.0
 */

// Requirements
require_once(dirname(dirname(__FILE__)) . '/include/sogi.session.class.php');

// Connect to database
$s = new SOGIsession(HOST, USER, PWD, DB_NAME);

if ( $s->exists($data->id) ) {

	// Check file list
	$list = glob(SPATH . '/' . $data->id . '/*');
	if ( 0 == count($list) ) die('{"err":4}');

	// Clean file list
	$fl = array();
	foreach ($list as $f) {
		// Remove path
		$f = explode('/', $f);
		$f = $f[count($f) - 1];

		if ( !in_array($f, $FILENAME_BAN) ) {
			$fs = explode('.', $f);

			// Get extension
			$ext = $fs[count($fs) - 1];
			// Get non-extension
			$fn = $fs;
			array_splice($fn, count($fn) - 1, 1);
			$fn = implode('.', $fn);

			// Add to $fl (0: to-convert, 1: converted)
			if ( isset($fl[$fn]) ) {
				if ( 'json' == $ext ) {
					$fl[$fn] = 1;
				}
			} else {
				if ( 'json' == $ext ) {
					$fl[$fn] = 1;
				} else {
					$fl[$fn] = 0;
				}
			}
		}
	}
	if ( 0 == count($fl) ) die('{"err":4}');

	// Prepare in JSON format
	$sfl = '';
	$i = 0;
	foreach ($fl as $k => $v) {
		if ( '' != $sfl ) $sfl .= ', ';
		$sfl .= '{"name":"' . $k . '","status":' . $v . ',"id":' . $i . '}';
		$i++;
	}

	// Answer call
	die('{"err":0, "list":[' . $sfl . ']}');

} else {
	die('{"err":3}');
}

?>
