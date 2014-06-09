<?php

require_once('SOGI-settings.php');

if(!isset($_GET['a'])) die('E0');

if(!isset($_POST['id']) or '' == @$_POST['id'] or !SOGIsession::is(@$_POST['id'])) die('E1');

$ss = new SOGIsession($FILENAME_BAN, $_POST['id']);
if(1 == $ss->get('running')) die('ER');

switch($_GET['a']) {
	case 'convertToJSON': {
		if(isset($_POST['name'])) {
			if('' != $_POST['name']) {
				$query = 'cd ' . INCL_PATH . 'Rscripts; ./convertToJSON.R ' . $_POST['id'] . ' ' . $_POST['name'];
				$res = SOGIsession::exec($FILENAME_BAN, $_POST['id'], 'convertToJSON', $query);
				if($res === FALSE) {
					die('E4');
				}
			} else {
				die('E3');
			}
		} else {
			die('E2');
		}
		break;
	}

	case 'loadGraph': {
		if(isset($_POST['name'])) {
			if('' != $_POST['name']) {
				$ss = new SOGIsession($FILENAME_BAN, $_POST['id']);
				if(!in_array($_POST['name'], $ss->getJSONFileList())) {
					die('E4');
				} else {
					echo file_get_contents(ROOT_URI . 'session/' . $_POST['id'] . '/' . $_POST['name'] . '.json');
					$ss->set('graph',$_POST['name']);
				}
			} else {
				die('E3');
			}
		} else {
			die('E2');
		}
		break;
	}

	case 'renameGraph': {
		if(isset($_POST['old_name']) and isset($_POST['new_name'])) {
			if('' != $_POST['old_name'] and '' != $_POST['new_name']) {
				$ss = new SOGIsession($FILENAME_BAN, $_POST['id']);
				if(!in_array($_POST['old_name'], $ss->getJSONFileList())) {
					die('E4');
				} else {
					if(in_array($_POST['new_name'], $ss->getJSONFileList())) {
						die('E5');
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
				die('E3');
			}
		} else {
			die('E2');
		}
		break;
	}

	case 'removeGraph': {
		if(isset($_POST['name'])) {
			if('' != $_POST['name']) {
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
		break;
	}

	case 'doConsole': {
		if(isset($_POST['text'])) {
			if('' != $_POST['text']) {
				file_put_contents(SESS_PATH . $_POST['id'] . '/CONSOLE', $_POST['text'], FILE_APPEND);
			} else {
				die('E3');
			}
		} else {
			die('E2');
		}
		break;
	}

	case 'isRunning': {
		return 0;
		break;
	}
}

?>