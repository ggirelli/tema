<?php
/**
 * Upload a network by drag&drop.
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

	// PARALLEL UPLOADS CODE

	// $responses = array();
	// $error_counter = 0;
	
	// for ($i = 0; $i < count($_FILES['files']['name']); $i++) { 
	// 	if( in_array(strtolower($_FILES['files']['name'][$i]), $FILENAME_BAN) ) {
	// 		// File cannot be uploaded (see FILENAME_BAN)
	// 		array_push($responses, '{"err":4');
	// 	}

	// 	$info = pathinfo($_FILES['files']['name'][$i]);
	// 	if(isset($info['extension']) and @in_array(strtolower($info['extension']), $ALLOWED_EXT)) {
	// 		$newname = $_FILES['files']['name'][$i]; 

	// 		$target = SPATH . '/' . $data->id . '/' . $newname;
	// 		if( !file_exists($target) ) {

	// 			move_uploaded_file( $_FILES['files']['tmp_name'][$i], $target);
	// 			// File uploaded correcty
	// 			array_push($responses, '{"err":0}');

	// 		} else {
	// 			// File already exists
	// 			array_push($responses, '{"err":6}');
	// 			$error_counter++;
	// 		}

	// 	} else {
	// 		// Wrong extension
	// 		array_push($responses, '{"err":5}');
	// 		$error_counter++;
	// 	}
	// }

	// if ( 0 != $error_counter ) {
	// 	header('HTTP/1.1 500 Internal Server Error');
	// 	header('Content-type: text/plain');
	// }

	// die('[' . implode(',', $responses) . ']');
	


	// NO PARALLEL UPLOADS
	
	if( in_array(strtolower($_FILES['files']['name']), $FILENAME_BAN) ) {
		// File cannot be uploaded (see FILENAME_BAN)
		header('HTTP/1.1 500 Internal Server Error');
		header('Content-type: text/plain');
		die('Files with this name cannot be uploaded.');
	}

	$info = pathinfo($_FILES['files']['name']);
	if(isset($info['extension']) and @in_array(strtolower($info['extension']), $ALLOWED_EXT)) {
		$newname = $_FILES['files']['name']; 

		$target = SPATH . '/' . $data->id . '/' . $newname;
		if( !file_exists($target) ) {

			move_uploaded_file( $_FILES['files']['tmp_name'], $target);
			// File uploaded correctly
			die('{"err":0,"msg":"File uploaded correctly"}');

		} else {
			// File already exists
			header('HTTP/1.1 500 Internal Server Error');
			header('Content-type: text/plain');
			die('File already uploaded, rename and retry.');
		}

	} else {
		// Wrong extension
		header('HTTP/1.1 500 Internal Server Error');
		header('Content-type: text/plain');
		die('You can\'t upload files of this type.');
	}

} else {
	header('HTTP/1.1 500 Internal Server Error');
	header('Content-type: text/plain');
	die('You are trying to upload files to a session that does not exist.');
}

?>
