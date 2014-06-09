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

	case 'loadGraph': {
		if(isset($_POST['id']) and isset($_POST['name'])) {
			if($_POST['id'] != '' and $_POST['name'] != '') {
				$ss = new SOGIsession($FILENAME_BAN, $_POST['id']);
				if(!in_array($_POST['name'], $ss->getJSONFileList())) {
					die('E3');
				} else {
					echo file_get_contents(ROOT_URI . 'session/' . $_POST['id'] . '/' . $_POST['name'] . '.json');
					$ss->set('graph',$_POST['name']);
				}
			} else {
				die('E2');
			}
		} else {
			die('E1');
		}
		break;
	}

	case 'renameGraph': {
		if(isset($_POST['id']) and isset($_POST['old_name']) and isset($_POST['new_name'])) {
			if($_POST['id'] != '' and $_POST['old_name'] != '' and $_POST['new_name'] != '') {
				$ss = new SOGIsession($FILENAME_BAN, $_POST['id']);
				if(!in_array($_POST['old_name'], $ss->getJSONFileList())) {
					die('E3');
				} else {
					if(in_array($_POST['new_name'], $ss->getJSONFileList())) {
						die('E4');
					} else {
						rename(SESS_PATH . $_POST['id'] . '/' . $_POST['old_name'] . '.json', SESS_PATH . $_POST['id'] . '/' . $_POST['new_name'] . '.json');
						rename(SESS_PATH . $_POST['id'] . '/' . $_POST['old_name'] . '.graphml', SESS_PATH . $_POST['id'] . '/' . $_POST['new_name'] . '.graphml');
						if($ss->get('graph') == $_POST['old_name']) {
							$ss->set('graph', $_POST['new_name']);
						}
						die('OK');
					}
				}
			} else {
				die('E2');
			}
		} else {
			die('E1');
		}
		break;
	}

	case 'removeGraph': {
		if(isset($_POST['id']) and isset($_POST['name'])) {
			if($_POST['id'] != '' and $_POST['name'] != '') {
				if(SOGIsession::is($_POST['id'])) {
					$ss = new SOGIsession($FILENAME_BAN, $_POST['id']);
					if(in_array($_POST['name'], $ss->getJSONFileList())) {
						unlink(SESS_PATH . $_POST['id'] . '/' . $_POST['name'] . '.json');
						unlink(SESS_PATH . $_POST['id'] . '/' . $_POST['name'] . '.graphml');
						die('OK');
					} else {
						die('E4');
					}
				} else {
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

	case 'doConsole': {
		if(isset($_POST['text']) and isset($_POST['id'])) {
			if($_POST['text'] != '' and $_POST['id'] != '') {
				file_put_contents(SESS_PATH . $_POST['id'] . '/CONSOLE', $_POST['text'], FILE_APPEND);
			} else {
				die('E2');
			}
		} else {
			die('E1');
		}
		break;
	}

	case 'isRunning': {
		if(isset($_POST['text']) and $_POST['id'] != '') {
			$ss = new SOGIsession($FILENAME_BAN, $_POST['id']);
			echo $ss->get('running');
		} else {
			die('E1');
		}
		break;
	}
}

?>