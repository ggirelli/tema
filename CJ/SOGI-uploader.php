<?php

require_once('SOGI-settings.php');

if(in_array($_FILES['file']['name'], $FILENAME_BAN)) {
	die('0');
}

$info = pathinfo($_FILES['file']['name']);
if(isset($info['extension']) and @in_array($info['extension'], array('graphml'))) {
	$newname = $_FILES['file']['name']; 

	$target = SESS_PATH . $_POST['id'] . '/' . $newname;
	move_uploaded_file( $_FILES['file']['tmp_name'], $target);
	
	die($_POST['id']);
} else {
	die('0');
}

?>