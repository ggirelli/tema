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

			case 'new_session': {
				require_once('action/session_new.php');
				break;
			}

			case 'load_session': {
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