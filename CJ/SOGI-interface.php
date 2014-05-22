<?php
$id = $_GET['id'];

require_once('SOGI-settings.php');

$ss = new SOGIsession($id);

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>SOGI - Simple Online Graph Interface - Session:<?php echo $id; ?></title>
	<script src="<?php echo ROOT_URI; ?>content/js/jquery.min.js"></script>
	<script src="<?php echo ROOT_URI; ?>content/js/bootstrap.min.js"></script>
	<link rel="stylesheet"  type="text/css" href="<?php echo ROOT_URI; ?>content/css/bootstrap.css" />
	<link rel="stylesheet"  type="text/css" href="<?php echo ROOT_URI; ?>content/css/main.css" />
	<link rel="stylesheet"  type="text/css" href="<?php echo ROOT_URI; ?>content/css/interface.css" />
</head>
<body>

<!-- Interfaccia, sessione <?php echo $id; ?>. -->

<div id='left-side' class="col-md-3">
	left sidebar
</div>

<div id="right-side" class="col-md-9">
	<div id="canvas" class="col-md-12">interface</div>
	<div id="bottom-side" class="col-md-12">bottom</div>
</div>

</body>
</html>