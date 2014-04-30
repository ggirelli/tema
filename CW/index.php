<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>SOGI - Simple Online Graph Interface</title>
	<script src="js/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<link rel="stylesheet"  type="text/css" href="css/bootstrap.css" />
	<link rel="stylesheet"  type="text/css" href="css/main.css" />
  <link rel="stylesheet"  type="text/css" href="css/bonjur.css" />

  <script type="text/javascript">

  $(document).ready(function() {
    $('#form #graphn').val('');
    $('#hidden-graphn').val('0');

    // Read the number of networks
    $('#form #graphn').change(function(e) {
      e.preventDefault();
      // Read number of networks
      var graphn = $(this).val();
      // If the number is not 'empty'
      if(graphn != '') {
        // Is that a number?
        if(/^[0-9]+$/.test(graphn)) {
          //Remove submit button
          $("#form input[type=submit]").remove();
          //Hide error
          $('#graphn-error').hide();
          if(graphn != $('#hidden-graphn').val()) {
            if(graphn > $('#hidden-graphn').val()) {
              // Show upload buttons
              for (var i = 0; i < graphn - $('#hidden-graphn').val(); i++) {
                $("<input type='file' class='graph-upload' id='graph-upload-" + (parseInt($('#hidden-graphn').val()) + i) + "' />").appendTo($('#form'));
              }
            } else {
              // Remove upload buttons
              for (var i = 1; i <= $('#hidden-graphn').val() - graphn; i++) {
                $('#graph-upload-' + (parseInt($('#hidden-graphn').val()) - i)).remove();
              }
            }
            // Store the old value
            $('#hidden-graphn').val(graphn);
            // Show submit button
            $("<input type='submit' class='btn btn-primary btn-block' value='Go on' />").appendTo($('#form'));
          }
        } else {
          // Show error
          $('#graphn-error').show();
        }
      } else {
        //Hide error
        $('#graphn-error').hide();
      }
    });
  });

  </script>

</head>
<body>
<input type='hidden' id='hidden-graphn' value='0' />

<h1 id='title'>SOGI</h1>

<form id='form' class='col-md-6 col-md-offset-3' method='post' action='interface.php'>
  First things first:<br />
  <input type='text' id='graphn' class='form-control' placeholder='how many graphs do you want to work on?' />
  <small id='graphn-error' class='text-danger console' style='display: none;'>Is that a <u>number</u>? Please write it with digits (i.e.: 0-9)</small>
</form>

</body>
</html>