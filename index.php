<?php
/*
 * Silence is golden
 */

#if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') { 

	require_once('user_side/src/index.html');

#} else {
#
#	// Redirect to HTTPS if communicating through HTTP
#	require_once('server_side/settings.php');
#	header('location: ' . RURI);
#
#}

?>

<!-- Ssssh! -->