<?php

require_once('SOGI-settings.php');

if(in_array($_FILES['file']['name'], array('CONFIG'))) {
	die(0);
}

$info = pathinfo($_FILES['file']['name']);
if(isset($info['extension']) and @$info['extension'] == 'graphml') {
	die(1);
} else {
	die(0);
}

?>