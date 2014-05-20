<?php

/*
 * Creates a random string with given length
 * @param int $l the desired length
 * @return String
 */
function random_string( $l ) {
	$s = "";

	# Generates a random string
	# (which length is a multiple of 32)
	for ( $i = 0; $i <= $l/32; $i++ )
		$s .= md5( time() + rand( 0, 99 ) );

	# Then shorten the random string
	# to the desired length
	while ( strlen( $s ) > $l ) {
		$i = (int) rand( 1, strlen( $s ) );
		$s = substr( $s, 0, $i ) . substr( $s, $i+1, strlen( $s ) - $i - 1 );
	}

	return $s;
}

?>