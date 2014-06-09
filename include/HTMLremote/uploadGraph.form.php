<?php

if(!isset($_POST['id']) or @$_POST['id'] == '') { die('<button id="abort-upload" class="btn btn-danger btn-block" onclick="javascript:hideJumbo();">abort</button>'); }

require_once(dirname(dirname(dirname(__FILE__))) . '/SOGI-settings.php');

?>

<style type="text/css">
	html, body {
		margin: 0;
		padding: 0;

		height: 100%;
		width: 100%;

		overflow: hidden;

		background-color: #f0f0f0;

		font-size: 16px;
	}

	.console {
	  font-family: monospace;
	  font-size: 0.6em;
	}

	.text-danger {
	  color: #C4221C;
	}

	body {
	  background-color: #F2EFDC;

	  color: #666666;
	  font-family: verdana;
	}

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

	#hidden-form {
	  display: none;
	}

	#panel-buttons {
	  padding: 0;
	}

	#panel-load {
	  margin-top: 2em;
	  padding: 0;
	  padding-top: 1.5em;

	  border-top: 1px solid #dadada;
	}

	#load-id {
	  margin: 0.5em 0;
	}
	
	#panel-welcome,
	#panel-interface {
		text-align: left;
		color: black;
	}

	#panel-welcome p,
	#panel-interface p {
		font-size: 1em;
	}
</style>

<script type="text/javascript">

	function checkFile(selector, index, triggerFunc) {
		var flist = $(selector);
		var filen = flist.length;
		if(filen != 0) {
			data = new FormData();
			data.append('file', flist[index].files[0]);

			$.ajax({
				type: 'POST',
				url: '<?php echo ROOT_URI; ?>fc/',
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
			session_id = '<?php  echo $_POST["id"]; ?>';
			interface_uri = <?php echo '\'' . ROOT_URI . 's/\''; ?> + session_id;

			// Set up progress interface
			$('#panel-interface').append($('<p />').html('We are uploading your files...<br />If you close this page you will block the upload and you will need to upload the files from the interface. Please, bookmark the following link if you want to close this page:<center><a href="' + interface_uri + '">' + interface_uri + '</a></center>'));
			var prog = $('<div/>').addClass('progress');
			$(prog).append($('<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;" />'));
			$(prog).appendTo('#panel-interface');

			// Upload files
			uploadFile($('#hidden-form input[type="file"]'), 0, session_id);
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
			url: '<?php echo ROOT_URI; ?>up/',
			data: data,
			success: function(data) {
				if(data == 0) {
					// Error
					$('#panel-interface').append($('<small />').text('An error occurred, the file upload has been blocked.<br />We are going to redirect you to the interface in 5 seconds.<br />Please try again from the later.').css({'text-align' : 'center', 'display' : 'block'}));
					setTimeout(function() { document.location.reload(); }, 5000);
				} else {
					// Add messages
					$('.progress-bar').attr('aria-valuenow', (100/filen)*(index+1)).css({'width' : (100/filen)*(index+1) + '%'});
					// Go on uploading
					if(index < filen-1) {
						uploadFile(flist, index+1, session_id);
					} else {
						$('#panel-interface').append($('<small />').text('Upload terminated, we are going to redirect you to the interface in 5 seconds.').css({'text-align' : 'center', 'display' : 'block'}));
						setTimeout(function() { document.location.reload(); }, 5000);
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
			checkFile('#hidden-form input[type="file"]:not(.checked)', 0, function() { return; });
		});

		// Set up upload button
		$('#start-upload').click(function(e) {
			// Check files
			checkFile('#hidden-form input[type="file"]:not(.checked)', 0, function() { preUploadFile(); return; });
		});
	});

</script>

<div class="page-header">
	<h1 id='title'>SOGI ~ uploader</h1>
</div>

<div class="panel col-md-6 col-md-offset-3">
	<div class="panel-body">
		<div id="panel-welcome">
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
				<button id="abort-upload" class="btn btn-danger btn-block" onclick="javascript:hideJumbo();">abort</button>
			</div>
			<div id="panel-list" class="col-md-8 panel-body"></div>
		</div>

	</div>
</div>

<form id='hidden-form'></form>
