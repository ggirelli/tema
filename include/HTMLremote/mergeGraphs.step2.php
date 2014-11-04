<?php

if(!isset($_POST['id']) or @$_POST['id'] == '') { die('<button id="abort-upload" class="btn btn-danger btn-block" onclick="javascript:hideJumbo();">abort</button>'); }

require_once(dirname(dirname(dirname(__FILE__))) . '/SOGI-settings.php');

$ss = new SOGIsession($FILENAME_BAN, $_POST['id']);

?>

<style type="text/css">
	#back-button,
	#next-next-button,
	#edit-button,
	#confirm-button {
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
		margin-bottom: 1.5em;
	}

	#vertex-attr-tab label,
	#edge-attr-tab label {
		line-height: 34px;
		color: #323232;
	}

    #edge-attr-title {
        display: inline-block;
    }

    #abort-upload {
    	margin-bottom: 2em;
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
			
			// Check if vertex ID attribute was selected
			if('#' == $('#vertex-main-attr').val()) {
				alert('Please, select a vertex-ID-attribute.');
			} else {
				// Show all forms without buttons and disable them
				$('#form-merge p').eq(0).html('Please, <u>verify</u> and <u>confirm</u>:')
				$('#form-merge, #form-merge-step-2').css({'display':'block'});
				$('#form-merge button, #form-merge input[type=submit], #form-merge-step-2 button, #form-merge-step-2 input[type=submit], #form-merge-step-2 input[type=button]').css({'display':'none'});
				$('#form-merge input, #form-merge select, #form-merge-step-2 input, #form-merge-step-2 select').attr('disabled', '');

				// Add buttons
				$('<span />').addClass('col-sm-5').append($('<button />').addClass('btn btn-block btn-info').attr('id', 'edit-button').text('back').click(function(e) {
					e.preventDefault();
					$('#form-merge p').eq(0).html('Select two graphs to merge:')
					$('#form-merge').css({'display':'none'});
					$('#form-merge button, #form-merge input[type=submit], #form-merge-step-2 button, #form-merge-step-2 input[type=submit], #form-merge-step-2 input[type=button]').css({'display':'block'});
					$('#form-merge input, #form-merge select, #form-merge-step-2 input, #form-merge-step-2 select').removeAttr('disabled');
					$('#form-merge-step-2 #vertex-attr-' + $('#form-merge-step-2 #vertex-main-attr').val() + ' select').attr('disabled', '');

					$('#edit-button, #confirm-button, #abort-upload-end').remove();
				})).insertAfter('#form-merge-step-2');

				$('<span />').addClass('col-sm-5 col-sm-offset-2').append($('<button />').addClass('btn btn-block btn-success').attr('id', 'confirm-button').text('confirm').click(function(e) {
					e.preventDefault();

					// Prepare vat
					vat = 'list\\(';
					$('#form-merge-step-2 #vertex-attr-tab select').each(function() {
						if('list\\(' != vat) { vat = vat + ','; }
						vat = vat + $(this).parent().siblings('label').text() + '=\\"' + $(this).val() + '\\"';
					});
					vat = vat + ',\\"ignore\\"\\)';

					// Prepare eat
					eat = 'list\\(';
					$('#form-merge-step-2 #edge-attr-tab select').each(function() {
						if('list\\(' != eat) { eat = eat + ','; }
						eat = eat + $(this).parent().siblings('label').text() + '=\\"' + $(this).val() + '\\"';
					});
					eat = eat + ',\\"ignore\\"\\)';

					// doServer
					doConsole('merge ' + $('#form-merge #first-graph').val() + ' ' + $('#form-merge #second-graph').val() + ' ' + $('#form-merge #output').val() + ' ' + $('#form-merge-step-2 #vertex-main-attr').val() + ' ' + vat + ' ' + eat);
					$('.jumbotron').css({'display':'none'});
					doServer('mergeGraphs', {'gone':$('#form-merge #first-graph').val(), 'gtwo':$('#form-merge #second-graph').val(), 'gout':$('#form-merge #output').val(), 'vkey':$('#form-merge-step-2 #vertex-main-attr').val(), 'vat':vat, 'eat':eat, 'id':'<?php echo $_POST["id"]; ?>'}, function(data) {
						console.log(data);
						if('DONE' == data) {
							doConsole('Merged.');
							convertGraphs([$('#form-merge #output').val()], 0);
							hideJumbo();
						} else {
							doConsole('Error, try again later.');
							hideJumbo();
						}
					});

				})).insertAfter($('#edit-button').parent());

				$('<button id="abort-upload-end" class="btn btn-danger btn-block" onclick="javascript:hideJumbo();">abort</button>').css({'margin':'3px 0'}).insertAfter($('#confirm-button').parent());
			}
		});

		// Get vertex id attribute event
		$('#vertex-main-attr').change(function(e) {
			if('#' != $(this).val()) {
				$('#vertex-attr-tab select').removeAttr('disabled').removeClass('main-id');
				$('#vertex-attr-tab #vertex-attr-' + $(this).val() + ' select').addClass('main-id').val('first').attr('disabled','');
			} else {
				// Enable all the vertex attributes
				$('#vertex-attr-tab select').removeAttr('disabled').removeClass('main-id');
			}
		});

		// Read graphs attributes
		var vattr = [], eattr = [];
		$.getJSON('<?php echo ROOT_URI; ?>session/<?php echo $_POST["id"]; ?>/' + $('#form-merge #first-graph').val() + '.json', {}, function(data) {
			$.each(data['nodes'][0]['data'], function(k,v) { vattr.push(k); });
			$.each(data['edges'][0]['data'], function(k,v) {
				if($.inArray(k, ['source', 'target']) == -1) {
					eattr.push(k);
				}
			});

			var actionSelect = $('<select />').addClass('form-control');
			$.each(['ignore', 'sum', 'prod', 'min', 'max', 'random', 'first', 'last', 'mean', 'median', 'concat'], function(k, v) {
				$(actionSelect).append($('<option />').val(v).text(v));
			});

			$.each(vattr, function(k, v) {
				$('<option />').val(v).text(v).appendTo('#vertex-main-attr');
				$('<div />').attr('id','vertex-attr-' + v).addClass('col-sm-12').append($('<label />').addClass('col-sm-3 control-label').text(v)).append($('<div />').addClass('col-sm-9').append($(actionSelect).clone())).appendTo('#vertex-attr-tab');
			});
			$.each(eattr, function(k, v) {
				$('<div />').attr('id','edge-attr-' + v).addClass('col-sm-12').append($('<label />').addClass('col-sm-3 control-label').text(v)).append($('<div />').addClass('col-sm-9').append($(actionSelect).clone())).appendTo('#edge-attr-tab');
			});
		});
	});
</script>

<form id="form-merge-step-2">
	<h3>Attributes panel</h3>
	<div class="col-sm-8 col-sm-offset-2" id='attributes-panel'>
		<select id="vertex-main-attr" name='vertex-main-attr' class='form-control'>
			<option value="#">Select the vertex ID attribute</option>
		</select>
		<p id='vertex-attr-title'><i>Vertex attributes</i></p>
		<div id="vertex-attr-tab"></div>
		<p id='edge-attr-title'><i>Edge attributes</i></p>
		<div id="edge-attr-tab"></div>
	</div>
	<span class="col-sm-5"><input type='button' id='back-button' class="btn btn-info btn-block" value='back' /></span>
	<span class="col-sm-5 col-sm-offset-2"><input type='submit' id='next-next-button' class="btn btn-info btn-block" value='next' /></span>
	<button id="abort-upload" class="btn btn-danger btn-block col-sm-12" onclick="javascript:hideJumbo();">abort</button>
</form>