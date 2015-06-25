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
define('DB_NAME', 'TEMA15');
define('SQL_TYPE', 'mysql');
#define('SQL_TYPE', 'psql');

// Address
define('RPATH', dirname(__FILE__));
define('SPATH', dirname(__FILE__) . '/session');
define('SCRIPATH', dirname(__FILE__) . '/Rscripts');
define('RURI', 'https://localhost/public_html/TEMA');

// Uploader
$GLOBALS['FILENAME_BAN'] = array('.', '..', 'config', 'sif.dat', 'sif.json');
$GLOBALS['ALLOWED_EXT'] = array('graphml');

?>
