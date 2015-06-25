<?php
/**
 * Selects the correct db class, either for pSQL or MySQL
 */

require_once('../settings.php');

if ( 'mysql' == SQL_TYPE ) {
	require_once('db.class.mysql.php');
}

if ( 'psql' == SQL_TYPE ) {
	require_once('db.class.psql.php');
}

?>
