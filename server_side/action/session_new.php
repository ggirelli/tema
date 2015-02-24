<?php
/**
 * Creates a new TEA session.
 * @author Gabriele Girelli <gabriele@filopoe.it>
 * @since  0.2.0
 */

// Requirements
require_once(dirname(dirname(__FILE__)) . '/include/tema.session.class.php');

// Connect to database
$s = new TEMAsession(HOST, USER, PWD, DB_NAME);

// Prepare new_session ID
$id = $s->new_id();

// Build new session
$s->init($id);

// Answer call
if ( $id == $s->get('id') ) {
	echo '{"err":0, "hash":"#/interface/' . $id . '"}';
} else {
	echo '{"err":3}';
}

?>
