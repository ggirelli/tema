<?php
/**
 * Connects user and server sides.
 */

// Requirements
require_once('settings.php');
require_once('include/sogi.session.class.php');

// Read POST JSON data
$data = json_decode(file_get_contents("php://input"));

if( isset($data->action) ) {

	if( '' != $data->action ) {

		switch($data->action) {

			case 'session_new': case 'session_load': case 'get_network_list': {
				require_once('action/' . $data->action . '.php');
				break;
			}

		}

	} else {
		die('{"err":2}');
	}

} else {
	die('{"err":1}');
}

?>