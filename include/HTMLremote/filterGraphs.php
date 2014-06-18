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

	.condition-tab-title {
		margin-top: 0.5em;
	}
	#add-condition-node,
	#add-condition-edge {
		margin-left: 18em;
	}

	.condition-row {
		margin: 0.5em 0;
	}
</style>

<script type="text/javascript">
	// Add condition tooltip
	$('#add-condition-node, #add-condition-edge').hover(function(e) {
		$(this).tooltip('toggle');
	});

	// Add conditions
	$('#add-condition-node').click(function(e) {
		e.preventDefault();
		basicCondition = $('<div />').addClass('col-md-12 condition-row');
		$('<span />').addClass('col-md-1 control-label').text('1)').css({'line-height':'34px','color':'#323232'}).appendTo($(basicCondition));
		$('<span />').addClass('col-md-4').append($('<select />').addClass('form-control')).appendTo($(basicCondition));
		selectCondition = $('<select />').addClass('form-control');
		$.each({'equal':'==', 'notequal':'!=', 'major':'>', 'eqmajor':'>=', 'minor':'<', 'eqminor':'<=', 'in':'in', 'notin':'!in'}, function(k, v) {
			console.log(k);
			console.log(v);
			$(selectCondition).append($('<option />').val(k).text(v));
		});
		$('<span />').addClass('col-md-2').css({'padding':'0'}).append($(selectCondition)).appendTo($(basicCondition));
		$('<span />').addClass('col-md-4').append($('<input type="text" />').addClass('form-control')).appendTo($(basicCondition));
		$('<button />').addClass('col-md-1 btn btn-warning').append($('<span />').addClass('glyphicon glyphicon-remove')).appendTo($(basicCondition));

		$(basicCondition).appendTo('#condition-node-tab');
	});
	$('#add-condition-edge').click(function(e) {
		e.preventDefault();
		basicCondition = $('<div />').addClass('col-md-12 condition-row');
		$('<span />').addClass('col-md-1 control-label').text('1)').css({'line-height':'34px','color':'#323232'}).appendTo($(basicCondition));
		$('<span />').addClass('col-md-4').append($('<select />').addClass('form-control')).appendTo($(basicCondition));
		$('<span />').addClass('col-md-3').append($('<select />').addClass('form-control')).appendTo($(basicCondition));
		$('<span />').addClass('col-md-3').append($('<input type="text" />').addClass('form-control')).appendTo($(basicCondition));
		$('<button />').addClass('col-md-1 btn btn-warning').append($('<span />').addClass('glyphicon glyphicon-remove')).appendTo($(basicCondition));

		$(basicCondition).appendTo('#condition-edge-tab');
	});

	// Submit 
	$('#form-filter').submit(function(e) {
		e.preventDefault();

	});
</script>

<div class="page-header">
	<h1 id='title'>SOGI <small>~ filter</small></h1>
</div>

<div class="panel col-md-6 col-md-offset-3">
	<div class="panel-body"><form id='form-filter'>
		
		<h3 class='condition-tab-title'>Filter <u>nodes</u> <button id="add-condition-node" class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="right" title="Add a condition"><span class='glyphicon glyphicon-plus'></span></button></h3>
		<div id="condition-node-tab"></div>
		<p>&nbsp;</p>

		<h3 class='condition-tab-title'>Filter <u>edges</u> <button id="add-condition-edge" class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="right" title="Add a condition"><span class='glyphicon glyphicon-plus'></span></button></h3>
		<div id="condition-edge-tab"></div>
		<p>&nbsp;</p>

		<button id="abort-upload" class="btn btn-danger btn-block" onclick="javascript:hideJumbo();" style="margin-top:1em;">abort</button>
	</form></div>
</div>
