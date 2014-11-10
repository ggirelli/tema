<?php
/**
 * Contains the settings for the SOGI instance.
 * @author Gabriele Girelli <gabriele@filopoe.it>
 * @since  0.2.0
 */

// MySQL connection
define('HOST', 'localhost');
define('USER', 'root');
define('PWD', '');
define('DB_NAME', 'SOGIv020');

// Address
define('RPATH', dirname(__FILE__));
define('SPATH', dirname(__FILE__) . '/session');
define('SCRIPATH', dirname(__FILE__) . '/Rscripts');
define('RURI', 'http://localhost/public_html/SOGIv020');

// Uploader
$GLOBALS['FILENAME_BAN'] = array('.', '..', 'config', 'sif.dat', 'sif.json');
$GLOBALS['ALLOWED_EXT'] = array('graphml');

?>
