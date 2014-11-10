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
	// Load session
	$s->init($data->id);

	$fl = $s->get('network_list');
	
	// Prepare in JSON format
	$sfl = '';
	$i = 0;
	foreach ($fl as $k => $v) {
		if ( '' != $sfl ) $sfl .= ', ';
		if ( isset($v->data) ) {
			$sfl .= '{"name":"' . $k . '","status":' . $v->status . ',"id":' . $i . ', "data":' . $v->data . '}';
		} else {
			$sfl .= '{"name":"' . $k . '","status":' . $v->status . ',"id":' . $i .  ', "data":{"e_count":"~","v_count":"~"}}';
		}
		$i++;
	}

	// Answer call
	die('{"err":0, "list":[' . $sfl . ']}');

} else {
	die('{"err":3}');
}

?>
