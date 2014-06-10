<?php

if(!isset($_POST['id']) or @$_POST['id'] == '') { die('<button id="abort-upload" class="btn btn-danger btn-block" onclick="javascript:hideJumbo();">abort</button>'); }

require_once(dirname(dirname(dirname(__FILE__))) . '/SOGI-settings.php');

$ss = new SOGIsession($FILENAME_BAN, $_POST['id']);

?>

<style type="text/css">
	#back-button,
	#next-next-button {
		margin: 2px 0;
	}

	span.col-sm-5 {
		padding: 0;
	}

	#form-merge-step-2,
	#form-merge-step-2 p {
		color: #323232;
	}

	#form-merge-step-2 p {
		margin-top: 1em;
	}

	#attributes-panel {
		margin-bottom: 0.5em;
	}
</style>

<script type='text/javascript'>
	$(document).ready(function() {
		// Set back event
		$('#back-button').click(function(e) {
			$('#form-merge-step-2').remove();
			$('#form-merge').css({'display':'block'});
		});

		// Set submit event
		$('#form-merge-step-2').submit(function(e) {
			e.preventDefault();
			alert('next');
		});

		// Read graphs attributes
		var vattr = [], eattr = [];
		$.getJSON('<?php echo ROOT_URI; ?>session/<?php echo $_POST["id"]; ?>/' + $('#form-merge #first-graph').val() + '.json', {}, function(data) {
			$.each(data['nodes'][0]['data'], function(k,v) { vattr.push(k); });
			$.each(data['edges'][0]['data'], function(k,v) { eattr.push(k); });

			console.log(vattr);
			console.log(eattr);
		});
	});
</script>

<form id="form-merge-step-2">
	<h3>Attributes panel</h3>
	<div class="col-sm-8 col-sm-offset-2" id='attributes-panel'>
		<select id="vertex-main-attr" name='vertex-main-attr' class='form-control'>
			<option value="#">Select the vertex ID attribute</option>
		</select>
		<p><i>Vertex attributes</i></p>
		<div id="vertex-attr-tab"></div>
		<p><i>Edge attributes</i></p>
		<div id="edge-attr-tab"></div>
	</div>
	<span class="col-sm-5"><input type='button' id='back-button' class="btn btn-success btn-block" value='back' /></span>
	<span class="col-sm-5 col-sm-offset-2"><input type='submit' id='next-next-button' class="btn btn-success btn-block" value='next' /></span>
	<button id="abort-upload" class="btn btn-danger btn-block col-sm-12" onclick="javascript:hideJumbo();">abort</button>
</form>