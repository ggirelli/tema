<?php

require_once('SOGI-settings.php');

if(!isset($_GET['a']) or !isset($_POST['id'])) die('E0');

switch($_GET['a']) {
	case 'convertToJSON': {
		if(isset($_POST['id']) and isset($_POST['name'])) {
			if($_POST['id'] != '' and $_POST['name'] != '') {
				$query = 'cd ' . INCL_PATH . 'Rscripts; ./convertToJSON.R ' . $_POST['id'] . ' ' . $_POST['name'];
				$res = SOGIsession::exec($FILENAME_BAN, $_POST['id'], 'convertToJSON', $query);
				if($res === FALSE) {
					die('E3');
				}
			} else {
				die('E2');
			}
		} else {
			die('E1');
		}
		break;
	}
}

?>