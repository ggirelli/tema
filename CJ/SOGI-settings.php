<?php

define('ROOT_URI', 'http://localhost/public_html/SOGI/CJ/');

define('ROOT_PATH', './');
define('CONT_PATH', ROOT_PATH . 'content/');
define('INCL_PATH', ROOT_PATH . 'include/');
define('SESS_PATH', ROOT_PATH . 'session/');

# Array of filenames that cannot be uploaded
$FILENAME_BAN = array('CONFIG', '.htaccess', 'php.ini');

require_once(INCL_PATH . 'functions.lib.php');
require_once(INCL_PATH . 'session.class.php');

?>