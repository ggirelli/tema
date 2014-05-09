<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>SOGI - Simple Online Graph Interface</title>
	<script src="content/js/jquery.min.js"></script>
	<script src="content/js/bootstrap.min.js"></script>

    <script type="text/javascript" src="content/js/json2.min.js"></script>
    <script type="text/javascript" src="content/js/AC_OETags.min.js"></script>
    <script type="text/javascript" src="content/js/cytoscapeweb.min.js"></script>

	<link rel="stylesheet"  type="text/css" href="content/css/bootstrap.css" />
	<link rel="stylesheet"  type="text/css" href="content/css/main.css" />
  <link rel="stylesheet"  type="text/css" href="content/css/interface.css" />

  <script type="text/javascript">
    $(document).ready(function() {
      // id of Cytoscape Web container div
      var div_id = "canvas";

      $.get('small.graphml', function(data) {
        // initialization options
        var options = {
          // where you have the Cytoscape Web SWF
          swfPath: "content/swf/CytoscapeWeb",
          // where you have the Flash installer SWF
          flashInstallerPath: "content/swf/playerProductInstall"
        };

        // init and draw
        var vis = new org.cytoscapeweb.Visualization(div_id, options);
        vis.draw({ network: data });
      })
    });
  </script>

</head>
<body>

<div id='left-side' class="col-sm-2"> left toolbar </div>
<div id="canvas" class="col-sm-10"></div>
<div id="bottom-side" class="col-sm-12"> bottom toolbar </div>

</body>
</html>