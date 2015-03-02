<?php
/**
 * Connects user and server sides.
 */

// Requirements
require_once('settings.php');
require_once('include/tema.session.class.php');

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

			case 'add_attr': case 'add_attr_index': case 'combine_attr':
			case 'map_gos': case 'add_ginfo_attrs':
			case 'remove_attr': case 'rename_attr':

			case 'networks_distances': case 'networks_intersect':
			case 'networks_merge': case 'networks_subtract':
			case 'network_contains':

			case 'get_settings': case 'get_setting_file':
			case 'set_settings': case 'upload_setting_file':

			case 'network_navigate':
			
			case 'get_network': case 'get_network_list':
			case 'convert_network': case 'save_network':
			case 'remove_network': case 'rename_network':
			case 'upload_network': case 'upload_drag_network':

			case 'create_session':
			case 'list_sessions': case 'enter_session':
			case 'update_history':
			case 'session_load':

			case 'register_user': case 'confirm_user':
			case 'login_user':

			{
				require_once('action/' . $data->action . '.php');
				break;
			}

			default: die('{"err":-1,"a":"' . $data->action . '"}');

		}

	} else {
		die('{"err":2}');
	}

} else {
	die('{"err":1}');
}

?>
