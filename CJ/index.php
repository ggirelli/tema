<?php

require_once('SOGI-settings.php');

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>SOGI - Simple Online Graph Interface</title>
	<script src="content/js/jquery.min.js"></script>
	<script src="content/js/bootstrap.min.js"></script>
	<link rel="stylesheet"  type="text/css" href="content/css/bootstrap.css" />
	<link rel="stylesheet"  type="text/css" href="content/css/main.css" />
	<link rel="stylesheet"  type="text/css" href="content/css/bonjur.css" />

	<script type="text/javascript">

	function checkFile(selector, index, triggerFunc) {
		var flist = $(selector);
		var filen = flist.length;
		if(filen != 0) {
			data = new FormData();
			data.append('file', flist[index].files[0]);

			$.ajax({
				type: 'POST',
				url: 'fc/',
				data: data,
				success: function(data) {
					if(data == 0) {
						$('#panel-list .alert-info').eq(index).children('button').remove();
						$('#panel-list .alert-info').eq(index).removeClass('alert-info alert-dismissable').addClass('alert-danger');
						$(selector).eq(index).remove();
						if($(selector).length != 0) {
							checkFile(selector, 0, triggerFunc);
						} else {
							if($('#panel-list .alert-success').length != 0) {
								triggerFunc();
							}
						}
					} else {
						$('#panel-list .alert-info').eq(index).removeClass('alert-info').addClass('alert-success');
						$(selector).eq(index).addClass('checked');
						if($(selector).length != 0) {
							checkFile(selector, 0, triggerFunc)
						} else {
							if($('#panel-list .alert-success').length != 0) {
								triggerFunc();
							}
						}
					}
				},
				processData: false,
				contentType: false
			});
		} else {
			if($('#panel-list .alert-success').length != 0) {
				triggerFunc();
			}
		}
	}

	function preUploadFile() {
		// Upload files
		if(!$('#panel-list').is(':empty')) {
			// Remove upload interface
			$('#panel-welcome').remove();
			$('#panel-buttons').remove();
			$('#panel-list').remove();

			// Ask for session_id
			$.ajax({
				type: 'GET',
				url: 'ssa/init',
				success: function(data) {
					session_id = data;
					interface_uri = <?php echo '\'' . ROOT_URI . 's/\''; ?> + session_id;

					// Set up progress interface
					$('#panel-interface').append($('<p />').html('We are uploading your files...<br />If you close this page you will block the upload and you will need to upload the files from the interface. Please, bookmark the following link if you want to close this page:<center><a href="' + interface_uri + '">' + interface_uri + '</a></center>'));
					var prog = $('<div/>').addClass('progress');
					$(prog).append($('<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;" />'));
					$(prog).appendTo('#panel-interface');

					// Upload files
					uploadFile($('#hidden-form input[type="file"]'), 0, session_id);
				},
				processData: false,
				contentType: false
			});
		} else {
			alert('Please, select at least ONE file to upload.');
		}
	}

	function uploadFile(flist, index, session_id) {
		var filen = flist.length;
		data = new FormData();
		data.append('file', flist[index].files[0]);
		data.append('id', session_id);

		$.ajax({
			type: 'POST',
			url: 'up/',
			data: data,
			success: function(data) {
				if(data == 0) {
					// Error
					$('#panel-interface').append($('<small />').text('An error occurred, the file upload has been blocked.<br />We are going to redirect you to the interface in 5 seconds.<br />Please try again from the interface uploader.').css({'text-align' : 'center', 'display' : 'block'}));
					setTimeout(function() { document.location.href = <?php echo '\'' . ROOT_URI . 's/\''; ?> + session_id; }, 5000);
				} else {
					// Add messages
					$('.progress-bar').attr('aria-valuenow', (100/filen)*(index+1)).css({'width' : (100/filen)*(index+1) + '%'});
					// Go on uploading
					if(index < filen-1) {
						uploadFile(flist, index+1, session_id);
					} else {
						$('#panel-interface').append($('<small />').text('Upload terminated, we are going to redirect you to the interface in 5 seconds.').css({'text-align' : 'center', 'display' : 'block'}));
						setTimeout(function() { document.location.href = <?php echo '\'' . ROOT_URI . 's/\''; ?> + session_id; }, 5000);
					}
				}
			},
			processData: false,
			contentType: false
		});
	}

	$(document).ready(function() {

		// Set up add_file button
		$('#add-file').click(function(e) {

			// Set up dismiss button
			var dismiss = $('<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>');
			$(dismiss).click(function(e) {
				$('#hidden-form input[type=file]').each(function() {
					if($(this).val() == $(dismiss).siblings('small').text()) {
						$(this).remove();
					}
				});
			});

			// Set up input[type=file]
			var fakeUp = $('<input type="file" />');
			$(fakeUp).change(function(e) {
				var wrap = $("<div/>").addClass('alert alert-info alert-dismissable');
				$(wrap).append($('<small/>').html($(this).val()));
				$(wrap).append(dismiss);
				$(wrap).appendTo('#panel-list');
				$(this).appendTo('#hidden-form');
			});
			$(fakeUp).trigger('click');
		});

		$('#check-file').click(function(e) {
			// Check files
			console.log(2);
			checkFile('#hidden-form input[type="file"]:not(.checked)', 0, function() { return; });
		});

		// Set up upload button
		$('#start-upload').click(function(e) {
			// Check files
			console.log(1);
			checkFile('#hidden-form input[type="file"]:not(.checked)', 0, function() { preUploadFile(); return; });
		});

		// Set up load button
		$('#load-session').submit(function(e) {
			e.preventDefault();
			$.ajax({
				type: 'GET',
				url: 'ssal/' + $('#load-id').val(),
				success: function(data) {
					switch(data) {
						case 'E0': {
							alert('An error occurred, please try again.\nIf the error persists, contact the admin.');
							break;
						}
						case 'E1': {
							alert('The requested session does not exist.');
							break;
						}
						case 'E2': {
							alert('Please, insert a session ID.');
							break;
						}
						default: {
							document.location.href = data;
						}
					}
				}
			});
		});
	
	});

	</script>

</head>
<body>
	<div class="page-header">
		<h1 id='title'>SOGI <small>Simple Online Graph Interface</small></h1>
	</div>

	<div class="panel col-md-6 col-md-offset-3">
		<div class="panel-body">
			<div id="panel-welcome">
				<p>Welcome to SOGI!</p>
				<p>
					Please, upload your <code>.graphml</code> files to <b>start a new session</b>!<br />
					<small>If your files are particularly</small> <b>BIG</b> <small>please, use an</small>	<code>sshfs</code><small> or </small> <code>scp</code><small> connection instead of this uploader.</small>
				</p>
			</div>
			
			<div id="panel-interface" class="panel">
				<div id="panel-buttons" class="col-md-4 panel-body">
					<button id="add-file" class="btn btn-primary btn-block">add a file</button>
					<button id="check-file" class="btn btn-warning btn-block">check files</button>
					<button id="start-upload" class="btn btn-success btn-block">start upload</button>
				</div>
				<div id="panel-list" class="col-md-8 panel-body"></div>
			</div>

			<div id="panel-load" class="col-md-12">
				Or <b>load an old session</b>:<br />
				<form id='load-session' target=''>
					<input id='load-id' type='text' class='form-control' placeholder='Enter session ID' />
					<input type='submit' class="btn btn-info col-md-4" value='load session' />
				</form>
			</div>
		</div>
	</div>

	<form id='hidden-form'></form>
</body>
</html>