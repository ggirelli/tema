<?php

foreach($_FILES as $input_id => $file) {
	echo $file['tmp_name'] . "\n";
}

?>