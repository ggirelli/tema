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
	$('#form-intersect').submit(function(e) {
		e.preventDefault();

		vone = $('#first-graph').val();
		vtwo = $('#second-graph').val();
		vout = $('#output').val();
		if('#' == vone || '#' == vtwo || vone == vtwo) {
			alert('Please select two different graphs.')
		} else {
			if('' == vout) { 
				alert('Please, specify an output file.');
			} else {
				// Check the output file
				if(/^([0-9a-zA-Z_-]*)$/.test(vout)) {
					$.post('<?php echo ROOT_URI; ?>doserve/isFile', {'id':'<?php echo $_POST["id"]; ?>', 'name':vout}, function(data) {
						if('1' == data) {
							alert('Please, change output file.');
						} else {
							// Intersect
							// doServer
							doConsole('intersect ' + $('#form-intersect #first-graph').val() + ' ' + $('#form-intersect #second-graph').val() + ' ' + $('#form-intersect #output').val());
							$('.jumbotron').css({'display':'none'});
							doServer('intersectGraphs', {'gone':$('#form-intersect #first-graph').val(), 'gtwo':$('#form-intersect #second-graph').val(), 'gout':$('#form-intersect #output').val(), 'id':'<?php echo $_POST["id"]; ?>'}, function(data) {
								console.log(data);
								if('DONE' == data) {
									doConsole('Intersected.');
									convertGraphs([$('#form-intersect #output').val()], 0);
									hideJumbo();
								} else {
									doConsole('Error, try again later.');
									hideJumbo();
								}
							});
						}
					}, 'html');
				} else {
					alert('Please, use only alfanumerics for the output file name.\nSpecial characters allowed are - _');
				}
			}
		}
	});
</script>

<div class="page-header">
	<h1 id='title'>SOGI <small>~ intersect</small></h1>
</div>

<?php if(count($lf) < 2) { ?>

<div class="panel col-md-6 col-md-offset-3">
	<div class="panel-body">
		<p>Can not intersect, the current session contains<br />only one graph...</p>
		<button id="abort-upload" class="btn btn-danger btn-block" onclick="javascript:hideJumbo();">abort</button>
	</div>
</div>

<?php die(); } ?>

<div class="panel col-md-6 col-md-offset-3">
	<div class="panel-body"><form id='form-intersect'>
		<p>Select two graphs to intersect:</p>
		<p><select id='first-graph' class="form-control">
			<option value="#">Select a graph</option>
			<?php foreach($lf as $fn) { ?><option value="<?php echo $fn; ?>"><?php echo $fn; ?></option><?php } ?>
		</select></p>
		<p><select id='second-graph' class="form-control">
			<option value="#">Select a graph</option>
			<?php foreach($lf as $fn) { ?><option value="<?php echo $fn; ?>"><?php echo $fn; ?></option><?php } ?>
		</select></p>
		<p><input type="text" id="output" class='form-control' placeholder='Output file' /></p>
		<input type='submit' id='intersect-button' class="btn btn-success btn-block" value='intersect' />
		<button id="abort-upload" class="btn btn-danger btn-block" onclick="javascript:hideJumbo();">abort</button>
	</form></div>
</div>
