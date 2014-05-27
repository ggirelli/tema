<?php

require_once('SOGI-settings.php');

# Is there an action?
if(isset($_GET['a']) and @$_GET['a'] != '') {

	switch($_GET['a']) {
		case 'load': {

			# Is there an ID?
			if(isset($_GET['id']) and @$_GET['id'] != '') {
				# is the ID correct?
				if(SOGIsession::is($_GET['id'])) {
					# Load session
					$session = new SOGIsession($FILENAME_BAN, $_GET['id']);
					# Retrieve ID
					$id = $session->get('uri');
					if($id != -1) die($id);
				}

				# Terminate
				die('E1');
			}

			# Terminate
			die('E2');
		}
		case 'init': {
			# Initialize session
			$session = new SOGIsession($FILENAME_BAN);
			# Retrieve ID
			$id = $session->get('id');
			if($id != -1) die($id);

			# Terminate
			die('E3');
		}
	}

}

# Terminate
die('E0');
?>