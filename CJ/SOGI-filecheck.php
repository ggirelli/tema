<?php

require_once('SOGI-settings.php');

$info = pathinfo($_FILES['file']['name']);
if(isset($info['extension']) and @$info['extension'] == 'graphml') {
	echo 1;
} else {
	echo 0;
}

?>