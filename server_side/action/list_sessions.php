<?php
/**
 * Lists TEMA sessions
 * @author Gabriele Girelli <gabriele@filopoe.it>
 * @since  0.3.0
 */

// Requirements
require_once(dirname(dirname(__FILE__)) . '/include/tema.session.class.php');
require_once(dirname(dirname(__FILE__)) . '/include/tema.user.class.php');

$user = new TEMAuser(
	HOST, USER, PWD, DB_NAME, TUModes::SIMPLE,
	$data->usr, NULL, NULL, NULL
);

switch($data->type) {
	case 'owned': {
		$list = $user->list_owned_sessions();

		if ( !$data->list ) {

			$json = '{"list":[';
			for ($i = 0; $i < count($list); $i++) {
				$session = $list[$i];
				$json .= '{"seed":"' . $session['seed'] .'", ' .
					'"title":"' . $session['title'] .'", ' .
					'"privacy":"' . $session['privacy'] .'", ' .
					'"password":"' . $session['password'] .'"}';
				if ( $i != count($list)-1 ) $json .= ",";
			}
			$json .= '], "err":0}';

			die($json);

		} else {

			$json = '{"list":[';
			for ($i = 0; $i < count($list); $i++) {
				$session = $list[$i];
				$json .= '"' . $session['seed'] .'"';
				if ( $i != count($list)-1 ) $json .= ",";
			}
			$json .= '], "err":0}';

			die($json);

		}

		break;
	}
	case 'shared': {
		$list = $user->list_shared_sessions();

		$json = '{"list":[';
		for ($i = 0; $i < count($list); $i++) {
			$session = $list[$i];
			$json .= '{"seed":"' . $session['seed'] .'", ' .
				'"title":"' . $session['title'] .'", ' .
				'"privacy":"' . $session['privacy'] .'", ' .
				'"password":"' . $session['password'] . '", ' .
				'"owner":"' . $session['nickname'] . '"' .
				'}';
			if ( $i != count($list)-1 ) $json .= ",";
		}
		$json .= '], "err":0}';

		die($json);

		break;
	}
	case 'history': {
		$list = $user->list_history_sessions();

		$json = '{"list":[';
		for ($i = 0; $i < count($list); $i++) {
			$session = $list[$i];
			$json .= '{"seed":"' . $session['seed'] .'", ' .
				'"title":"' . $session['title'] .'", ' .
				'"privacy":"' . $session['privacy'] .'", ' .
				'"password":"' . $session['password'] .'", ' .
				'"date":"' . $session['date'] . '"}';
			if ( $i != count($list)-1 ) $json .= ",";
		}
		$json .= '], "err":0}';

		die($json);

		break;
	}
}

die('{"err":3, "list":[]}');

?>