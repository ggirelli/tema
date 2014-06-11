<?php

if(!isset($_POST['id']) or @$_POST['id'] == '') { die('<button id="abort-upload" class="btn btn-danger btn-block" onclick="javascript:hideJumbo();">abort</button>'); }

require_once(dirname(dirname(dirname(__FILE__))) . '/SOGI-settings.php');

$ss = new SOGIsession($FILENAME_BAN, $_POST['id']);
$lf = $ss->getJSONFileList();

?>

<style type="text/css">
	.page-header {
	  border: none;

	  color: #F24C27;
	  font-family: 'optimus';
	  text-align: center;
	}
	.page-header h1 {
	  font-size: 5em;
	}
	.page-header small {
	  font-size: 0.6em;
	}

	.panel-body {
		color: #323232;
	}
	.panel-body select {
	    font-family: arial;
	    color: #323232;
	    
	}
</style>

<script type="text/javascript">
	$('#form-filter').submit(function(e) {
		e.preventDefault();

	});
</script>

<div class="page-header">
	<h1 id='title'>SOGI <small>~ filter</small></h1>
</div>

<div class="panel col-md-6 col-md-offset-3">
	<div class="panel-body"><form id='form-filter'>
		<p>@TODO</p>
		<button id="abort-upload" class="btn btn-danger btn-block" onclick="javascript:hideJumbo();">abort</button>
	</form></div>
</div>
