<?php

require_once('SOGI-settings.php');

if(!isset($_GET['a'])) die('E0');

if(!isset($_POST['id']) or '' == @$_POST['id'] or !SOGIsession::is(@$_POST['id'])) die('E1');

$ss = new SOGIsession($FILENAME_BAN, $_POST['id']);
if('isRunning' == $_GET['a']) {
	$query = 'cd ' . SESS_PATH . $_POST['id'] .  '/; cat CONFIG | head -n 4 | tail -n 1 | cut -f 2';
	$res = SOGIsession::hiddenExecReturn($FILENAME_BAN, $_POST['id'], 'convertToJSON', $query);
	die($res[0]);
}
if(1 == $ss->get('running')) die('ER');

switch($_GET['a']) {
	case 'convertToJSON': {
		if(isset($_POST['name'])) {
			if('' != $_POST['name']) {
				$query = 'cd ' . INCL_PATH . 'Rscripts; ./convertToJSON.R ' . $_POST['id'] . ' ' . $_POST['name'];
				$res = SOGIsession::exec($FILENAME_BAN, $_POST['id'], 'convertToJSON', $query);
				if($res === FALSE) {
					die('E4');
				} else {
					die('OK');
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
					$ss->set('running',1);
					echo file_get_contents(ROOT_URI . 'session/' . $_POST['id'] . '/' . $_POST['name'] . '.json');
					$ss->set('graph',$_POST['name']);
					$ss->set('running',0);
					die();
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
						$ss->set('running',1);
						rename(SESS_PATH . $_POST['id'] . '/' . $_POST['old_name'] . '.json', SESS_PATH . $_POST['id'] . '/' . $_POST['new_name'] . '.json');
						rename(SESS_PATH . $_POST['id'] . '/' . $_POST['old_name'] . '.graphml', SESS_PATH . $_POST['id'] . '/' . $_POST['new_name'] . '.graphml');
						if($ss->get('graph') == $_POST['old_name']) {
							$ss->set('graph', $_POST['new_name']);
						}
						$ss->set('running',0);
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
				$ss->set('running',1);
				$ss = new SOGIsession($FILENAME_BAN, $_POST['id']);
				if(in_array($_POST['name'], $ss->getJSONFileList())) {
					unlink(SESS_PATH . $_POST['id'] . '/' . $_POST['name'] . '.json');
					unlink(SESS_PATH . $_POST['id'] . '/' . $_POST['name'] . '.graphml');
					$ss->set('graph',0);
					$ss->set('running',0);
					die('OK');
				} else {
					$ss->set('running',0);
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

	case 'mergeGraphs': {
		if(isset($_POST['gone']) and isset($_POST['gtwo']) and isset($_POST['gout']) and isset($_POST['vkey']) and isset($_POST['vat']) and isset($_POST['eat'])) {
			if('' != $_POST['gone'] and '' != $_POST['gtwo'] and '' != $_POST['gout'] and '' != $_POST['vkey'] and '' != $_POST['vat'] and '' != $_POST['eat']) {
				$query = 'cd ' . INCL_PATH . 'Rscripts; ./mergeGraphs.R ' . $_POST['id'] . ' ' . $_POST['gone'] . ' ' . $_POST['gtwo'] . ' ' . $_POST['gout'] . ' ' . $_POST['vkey'] . ' ' . $_POST['vat'] . ' ' . $_POST['eat'];
				$res = SOGIsession::exec($FILENAME_BAN, $_POST['id'], 'mergeGraphs', $query);
				if($res === FALSE) {
					die('E4');
				} else {
					die('DONE');
				}
			} else {
				die('E3');
			}
		} else {
			die('E2');
		}
		break;
	}

	case 'intersectGraphs': {
		if(isset($_POST['gone']) and isset($_POST['gtwo']) and isset($_POST['gout'])) {
			if('' != $_POST['gone'] and '' != $_POST['gtwo'] and '' != $_POST['gout']) {
				$query = 'cd ' . INCL_PATH . 'Rscripts; ./intersectGraphs.R ' . $_POST['id'] . ' ' . $_POST['gone'] . ' ' . $_POST['gtwo'] . ' ' . $_POST['gout'];
				$res = SOGIsession::exec($FILENAME_BAN, $_POST['id'], 'intersectGraphs', $query);
				if($res === FALSE) {
					die('E4');
				} else {
					die('DONE');
				}
			} else {
				die('E3');
			}
		} else {
			die('E2');
		}
		break;
	}

	case 'subtractGraphs': {
		if(isset($_POST['gone']) and isset($_POST['gtwo']) and isset($_POST['gout'])) {
			if('' != $_POST['gone'] and '' != $_POST['gtwo'] and '' != $_POST['gout']) {
				$query = 'cd ' . INCL_PATH . 'Rscripts; ./subtractGraphs.R ' . $_POST['id'] . ' ' . $_POST['gone'] . ' ' . $_POST['gtwo'] . ' ' . $_POST['gout'];
				$res = SOGIsession::exec($FILENAME_BAN, $_POST['id'], 'subtractGraphs', $query);
				if($res === FALSE) {
					die('E4');
				} else {
					die('DONE');
				}
			} else {
				die('E3');
			}
		} else {
			die('E2');
		}
		break;
	}

	case 'containsGraphs': {
		if(isset($_POST['gone']) and isset($_POST['gtwo'])) {
			if('' != $_POST['gone'] and '' != $_POST['gtwo']) {
				$query = 'cd ' . INCL_PATH . 'Rscripts; ./containsGraphs.R ' . $_POST['id'] . ' ' . $_POST['gone'] . ' ' . $_POST['gtwo'];
				$res = SOGIsession::execReturn($FILENAME_BAN, $_POST['id'], 'containsGraphs', $query);
				if($res === 'ERROR') {
					die('E4');
				} else {
					switch($res[count($res)-1]) {
						case 'Y>': {
							die('Y');
							break;
						}
						case 'N>': {
							die('N');
							break;
						}
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

	case 'isFile': {
		if(isset($_POST['name'])) {
			if('' != $_POST['name']) {
				if(in_array($_POST['name'], $ss->getGraphmlFileList())) {
					die('1');
				} else {
					die('0');
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
				$ss->set('running',1);
				file_put_contents(SESS_PATH . $_POST['id'] . '/CONSOLE', $_POST['text'], FILE_APPEND);
				$ss->set('running',0);
			} else {
				die('E3');
			}
		} else {
			die('E2');
		}
		break;
	}

	case 'applySetting': {
		if(isset($_POST['name']) and isset($_POST['value'])) {
			if('' != $_POST['name'] and '' != $_POST['value']) {
				if(in_array($_POST['name'], array("running", "last", "time", "graph", "nodesThreshold"))) {
					$ss = new SOGIsession($FILENAME_BAN, $_POST['id']);
					$ss->set($_POST['name'], $_POST['value']);
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
	}
}

?>
ERROR
