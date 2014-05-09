<?php

require_once('include/session.class.php');

// Session
$ss = new SOGIsession();
$ss->init();

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

  $(document).ready(function() {

    // Set up add_file button
    $('#add-file').click(function(e) {
      e.defaultPrevented;

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

    // Set up upload button
    $('#start-upload').click(function(e) {

      // Remove upload interface
      $('#panel-welcome').remove();
      $('#panel-buttons').remove();
      $('#panel-list').remove();

      // Ask for session_id

      // Set up progress interface
      $('#panel-interface').append($('<p />').html('We are uploading your files...<br />If you close this page you will block the upload and you will need to upload the files from the interface. Please, bookmark the following link if you want to close this page: <a href="">link</a><br />'));
      var prog = $('<div/>').addClass('progress');
      $(prog).append($('<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;" />'));
      $(prog).appendTo('#panel-interface');

      // Upload files
      var filen = $('#hidden-form input[type="file"]').length;
      $('#hidden-form input[type="file"]').each(function(index) {
        data = new FormData();
        data.append('file', $(this)[0].files[0]);

        $.ajax({
          type: 'POST',
          url: 'graph_loader.php',
          data: data,
          success: function(data) {
            alert(data)
            // Add messages
            $('.progress-bar').attr('aria-valuenow', (100/filen)*(index+1)).css({'width' : (100/filen)*(index+1) + '%'});
          },
          processData: false,
          contentType: false
        });

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
      <p>Please, upload your graphml files to enter the interface!</p>
    </div>
    
    <div id="panel-interface" class="panel">
      <div id="panel-buttons" class="col-md-4 panel-body">
        <button id="add-file" class="btn btn-primary btn-block">add a file</button>
        <button id="start-upload" class="btn btn-success btn-block">start upload</button>
      </div>
      <div id="panel-list" class="col-md-8 panel-body">
        
      </div>
    </div>
  </div>
</div>

<form id='hidden-form'></form>

</body>
</html>