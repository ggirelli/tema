<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>SOGI - Simple Online Graph Interface</title>
	<script src="js/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>

    <script type="text/javascript" src="js/json2.min.js"></script>
    <script type="text/javascript" src="js/AC_OETags.min.js"></script>
    <script type="text/javascript" src="js/cytoscapeweb.min.js"></script>

	<link rel="stylesheet"  type="text/css" href="css/bootstrap.css" />
	<link rel="stylesheet"  type="text/css" href="css/main.css" />
  <link rel="stylesheet"  type="text/css" href="css/interface.css" />

  <script type="text/javascript">
    window.onload=function() {
      // id of Cytoscape Web container div
      var div_id = "canvas";

      // you could also use other formats (e.g. GraphML) or grab the network data via AJAX
      var networ_json = {
        data: {
          nodes: [ { id: "1" }, { id: "2" } ],
          edges: [ { id: "2to1", target: "1", source: "2" } ]
        }
      };

      $.get('small.graphml', function(data) {
        // initialization options
        var options = {
          // where you have the Cytoscape Web SWF
          swfPath: "swf/CytoscapeWeb",
          // where you have the Flash installer SWF
          flashInstallerPath: "swf/playerProductInstall"
        };

        // init and draw
        var vis = new org.cytoscapeweb.Visualization(div_id, options);
        vis.draw({ network: data });
      })
    };
  </script>

</head>
<body>

<div id='left-side' class="col-sm-2"> left toolbar </div>
<div id="canvas" class="col-sm-10"></div>
<div id="bottom-side" class="col-sm-12"> bottom toolbar </div>

</body>
</html>