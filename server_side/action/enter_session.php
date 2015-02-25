<?php
/**
 * Lists TEMA sessions
 * @author Gabriele Girelli <gabriele@filopoe.it>
 * @since  0.3.0
 */

// Requirements
require_once(dirname(dirname(__FILE__)) . '/include/tema.session.class.php');
require_once(dirname(dirname(__FILE__)) . '/include/tema.user.class.php');

$s = new TEMAsession(HOST, USER, PWD, DB_NAME);
$s->init($data->seed);

switch($s->get('privacy')) {
	case 'private': {

		$u = new TEMAuser(
			HOST, USER, PWD, DB_NAME, TUModes::SIMPLE,
			$data->usr, NULL, NULL, NULL
		);

		$owned = array();
		foreach ($u->list_owned_sessions() as $session) {
			$owned[] = $session['seed'];
		}

		if ( !in_array($data->seed, $owned) ) {
			// Does not have the access privilege
			die('{"err":4}');
		}
		break;
	}
	case 'public': {
		break;
	}
	default: {
		// Wrong privacy status
		die('{"err":3}');
	}
}

switch($s->get('protected')) {
	case TRUE: {
		if($s->is_password($data->pwd)) {
			// Continue
			die('{"err":0}');
		} else {
			// Wrong password
			die('{"err":6}');
		}

		break;
	}
	case FALSE: {
		// Continue
		die('{"err":0}');
		break;
	}
	default: {
		// Wrong protection status
		die('{"err":5}');
	}
}


die('{"err":-2,"msg":"working","privacy":"' . $s->get('privacy') . '"}');

?>