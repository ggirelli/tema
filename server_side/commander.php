<?php
/**
 * Connects user and server sides.
 */

// Requirements
require_once('settings.php');
require_once('include/sogi.session.class.php');
$session = new SOGIsession(HOST, USER, PWD, DB_NAME, 1);

// Read POST JSON data
$data = json_decode(file_get_contents("php://input"));

if( isset($data->action) ) {

	if( '' != $data->action ) {

		switch($data->action) {

			case 'new_session': {
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