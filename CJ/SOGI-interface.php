<?php
$id = $_GET['id'];

require_once('SOGI-settings.php');

$ss = new SOGIsession($id);

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>SOGI - Simple Online Graph Interface - Session:<?php echo $id; ?></title>
	<script src="<?php echo ROOT_URI; ?>content/js/jquery.min.js"></script>
	<script src="<?php echo ROOT_URI; ?>content/js/bootstrap.min.js"></script>
	<script src="<?php echo ROOT_URI; ?>content/js/cytoscape.min.js"></script>
	<link rel="stylesheet"  type="text/css" href="<?php echo ROOT_URI; ?>content/css/bootstrap.css" />
	<link rel="stylesheet"  type="text/css" href="<?php echo ROOT_URI; ?>content/css/main.css" />
	<link rel="stylesheet"  type="text/css" href="<?php echo ROOT_URI; ?>content/css/interface.css" />

	<script type="text/javascript">

	$(document).ready(function() {
		$('#cy').cytoscape({

		  style: cytoscape.stylesheet()
		    .selector('node')
		      .css({
		        'content': 'data(name)',
		        'text-valign': 'center',
		        'color': 'white',
		        'text-outline-width': 2,
		        'text-outline-color': '#888'
		      })
		    .selector('edge')
		      .css({
		        'target-arrow-shape': 'triangle'
		      })
		    .selector(':selected')
		      .css({
		        'background-color': 'black',
		        'line-color': 'black',
		        'target-arrow-color': 'black',
		        'source-arrow-color': 'black'
		      })
		    .selector('.faded')
		      .css({
		        'opacity': 0.25,
		        'text-opacity': 0
		      }),
		  
		  elements: {
		    nodes: [
		      { data: { id: 'j', name: 'Jerry' } },
		      { data: { id: 'e', name: 'Elaine' } },
		      { data: { id: 'k', name: 'Kramer' } },
		      { data: { id: 'g', name: 'George' } }
		    ],
		    edges: [
		      { data: { source: 'j', target: 'e' } },
		      { data: { source: 'j', target: 'k' } },
		      { data: { source: 'j', target: 'g' } },
		      { data: { source: 'e', target: 'j' } },
		      { data: { source: 'e', target: 'k' } },
		      { data: { source: 'k', target: 'j' } },
		      { data: { source: 'k', target: 'e' } },
		      { data: { source: 'k', target: 'g' } },
		      { data: { source: 'g', target: 'j' } }
		    ]
		  },
		  
		  ready: function(){
		    window.cy = this;
		    
		    // giddy up...
		    
		    cy.elements().unselectify();
		    
		    cy.on('tap', 'node', function(e){
		      var node = e.cyTarget; 
		      var neighborhood = node.neighborhood().add(node);
		      
		      cy.elements().addClass('faded');
		      neighborhood.removeClass('faded');
		    });
		    
		    cy.on('tap', function(e){
		      if( e.cyTarget === cy ){
		        cy.elements().removeClass('faded');
		      }
		    });
		  }

		});
	});

	</script>
</head>
<body>

<!-- Interfaccia, sessione <?php echo $id; ?>. -->

<div id='left-side' class="col-md-2">
	left sidebar
</div>

<div id="right-side" class="col-md-10">
	<div id="canvas" class="col-md-12">
		<div id="cy"></div>
	</div>
	<div id="bottom-side" class="col-md-12">
		<div id="console" class="col-md-9">
			Console
		</div>
		<div id="inspector" class="col-md-3">
			Inspector
		</div>
	</div>
</div>

</body>
</html>