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

	$responses = array();
	print_r($_FILES);
	for ($i = 0; $i < count($_FILES['files']['name']); $i++) { 
		if( in_array(strtolower($_FILES['files']['name'][$i]), $FILENAME_BAN) ) {
			// File cannot be uploaded (see FILENAME_BAN)
			array_push($responses, '{"err":4');
		}

		$info = pathinfo($_FILES['files']['name'][$i]);
		if(isset($info['extension']) and @in_array(strtolower($info['extension']), $ALLOWED_EXT)) {
			$newname = $_FILES['files']['name'][$i]; 

			$target = SPATH . '/' . $data->id . '/' . $newname;
			if( !file_exists($target) ) {

				move_uploaded_file( $_FILES['files']['tmp_name'][$i], $target);
				// File uploaded correcty
				array_push($responses, '{"err":0}');

			} else {
				// File already exists
				array_push($responses, '{"err":6}');
			}

		} else {
			// Wrong extension
			array_push($responses, '{"err":5}');
		}
	}

	die('[' . implode(',', $responses) . ']');

} else {
	die('{"err":3}');
}

?>
