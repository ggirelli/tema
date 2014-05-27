<?php

require_once('SOGI-settings.php');

if(in_array($_FILES['file']['name'], $FILENAME_BAN)) {
	die('0');
}

$info = pathinfo($_FILES['file']['name']);
if(isset($info['extension']) and @in_array($info['extension'], array('graphml', 'json'))) {
	die('1');
} else {
	die('0');
}

?>