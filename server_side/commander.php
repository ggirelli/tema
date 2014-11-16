<?php
/**
 * Connects user and server sides.
 */

// Requirements
require_once('settings.php');
require_once('include/sogi.session.class.php');

if( isset($_POST['action']) ) {

	// Move POST essentials to $data
	$data = new stdClass;
	$data->action = $_POST['action'];

} else {

	// Read POST JSON data
	$data = json_decode(file_get_contents("php://input"));
	
}

if( isset($data->action) ) {

	if( '' != $data->action ) {

		switch($data->action) {

			case 'add_attr': case 'add_attr_index':
			case 'combine_attr': case 'convert_network':
			case 'get_network': case 'get_network_list':
			case 'get_settings': case 'get_sif':
			case 'networks_intersect': case 'networks_merge':
			case 'remove_attr': case 'remove_network':
			case 'rename_attr': case 'rename_network':
			case 'save_network':
			case 'session_new': case 'session_load':
			case 'set_settings':
			case 'upload_network': case 'upload_sif': {
				require_once('action/' . $data->action . '.php');
				break;
			}

			default: die('{"err":-1}');

		}

	} else {
		die('{"err":2}');
	}

} else {
	die('{"err":1}');
}

?>
