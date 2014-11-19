<?php
/**
 * Upload a network.
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

	if( in_array(strtolower($_FILES['file']['name']), $FILENAME_BAN) ) {
		// File cannot be uploaded (see FILENAME_BAN)
		die('{"err":4');
	}

	$info = pathinfo($_FILES['file']['name']);
	if(isset($info['extension']) and @in_array(strtolower($info['extension']), $ALLOWED_EXT)) {
		$newname = $_FILES['file']['name']; 

		$target = SPATH . '/' . $data->id . '/' . $newname;
		if( !file_exists($target) ) {

			move_uploaded_file( $_FILES['file']['tmp_name'], $target);
			// File uploaded correcty
			die('{"err":0}');

		} else {
			// File already exists
			die('{"err":6}');
		}

	} else {
		// Wrong extension
		die('{"err":5}');
	}

} else {
	echo '{"err":3}';
}

?>
