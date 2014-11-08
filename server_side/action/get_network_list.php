<?php
/**
 * Loads session network list.
 * @author Gabriele Girelli <gabriele@filopoe.it>
 * @since  0.2.0
 */

// Requirements
require_once(dirname(dirname(__FILE__)) . '/include/sogi.session.class.php');

// Connect to database
$s = new SOGIsession(HOST, USER, PWD, DB_NAME);

if ( $s->exists($data->id) ) {

	// Answer call
	echo '{"err":0, "list":null}';

} else {
	echo '{"err":3}';
}

?>