<?php

require_once('SOGI-settings.php');

# Check again required session ID
if(isset($_GET['id']) and @$_GET['id'] != '') {
	# Is the ID correct?
	if(SOGIsession::is($_GET['id'])) {
		# Load session
		$id = $_GET['id'];
		$ss = new SOGIsession($FILENAME_BAN, $id);
	} else {
		# Terminate
		die('E1');
	}
} else {
	# Terminate
	die('E2');
}

#print_r($ss->getCurrFileList());

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

<!-- SOGI, session <?php echo $id; ?> -->

<div id='left-side' class="col-md-2 panel-group">
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h4 class="panel-title"><a href="#graph-list" data-toggle="collapse" data-parent="#left-side">Graph List</a></h4>
		</div>
		<div class="panel-collapse collapse" id="graph-list">
			<div class="panel-body">
			<?php
			foreach($ss->getCurrFileList() as $fname) {
				echo "<a href=''>$fname</a><br />";
			}
			?>
			</div>
		</div>
	</div>
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h4 class="panel-title"><a href="#graph-tools" data-toggle="collapse" data-parent="#left-side">Graph Tools</a></h4>
		</div>
		<div class="panel-collapse collapse" id="graph-tools">
			<div class="panel-body">
				Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed, exercitationem, veritatis dolorum maxime corporis expedita a ad error! Rem, nostrum doloribus illo vero dolorum ea velit reprehenderit inventore et labore? Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed, exercitationem, veritatis dolorum maxime corporis expedita a ad error! Rem, nostrum doloribus illo vero dolorum ea velit reprehenderit inventore et labore? Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed, exercitationem, veritatis dolorum maxime corporis expedita a ad error! Rem, nostrum doloribus illo vero dolorum ea velit reprehenderit inventore et labore? Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed, exercitationem, veritatis dolorum maxime corporis expedita a ad error! Rem, nostrum doloribus illo vero dolorum ea velit reprehenderit inventore et labore? Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed, exercitationem, veritatis dolorum maxime corporis expedita a ad error! Rem, nostrum doloribus illo vero dolorum ea velit reprehenderit inventore et labore? Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed, exercitationem, veritatis dolorum maxime corporis expedita a ad error! Rem, nostrum doloribus illo vero dolorum ea velit reprehenderit inventore et labore? Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed, exercitationem, veritatis dolorum maxime corporis expedita a ad error! Rem, nostrum doloribus illo vero dolorum ea velit reprehenderit inventore et labore? Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed, exercitationem, veritatis dolorum maxime corporis expedita a ad error! Rem, nostrum doloribus illo vero dolorum ea velit reprehenderit inventore et labore?
			</div>
		</div>
	</div>
	<div class="panel panel-info">
		<div class="panel-heading">
			<h4 class="panel-title"><a href="#graph-settings" data-toggle="collapse" data-parent="#left-side">Settings</a></h4>
		</div>
		<div class="panel-collapse collapse" id="graph-settings">
			<div class="panel-body">
				Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed, exercitationem, veritatis dolorum maxime corporis expedita a ad error! Rem, nostrum doloribus illo vero dolorum ea velit reprehenderit inventore et labore? Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed, exercitationem, veritatis dolorum maxime corporis expedita a ad error! Rem, nostrum doloribus illo vero dolorum ea velit reprehenderit inventore et labore? Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed, exercitationem, veritatis dolorum maxime corporis expedita a ad error! Rem, nostrum doloribus illo vero dolorum ea velit reprehenderit inventore et labore? Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed, exercitationem, veritatis dolorum maxime corporis expedita a ad error! Rem, nostrum doloribus illo vero dolorum ea velit reprehenderit inventore et labore? Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed, exercitationem, veritatis dolorum maxime corporis expedita a ad error! Rem, nostrum doloribus illo vero dolorum ea velit reprehenderit inventore et labore? Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed, exercitationem, veritatis dolorum maxime corporis expedita a ad error! Rem, nostrum doloribus illo vero dolorum ea velit reprehenderit inventore et labore? Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed, exercitationem, veritatis dolorum maxime corporis expedita a ad error! Rem, nostrum doloribus illo vero dolorum ea velit reprehenderit inventore et labore? Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed, exercitationem, veritatis dolorum maxime corporis expedita a ad error! Rem, nostrum doloribus illo vero dolorum ea velit reprehenderit inventore et labore?
			</div>
		</div>
	</div>
	<div class="panel panel-success">
		<div class="panel-heading">
			<h4 class="panel-title"><a href="<?php echo ROOT_URI; ?>content/help" target='new'>Help</a></h4>
		</div>
	</div>
</div>

<div id="right-side" class="col-md-10">
	<div id="canvas" class="col-md-12">
		<div id="cy"></div>
	</div>
	<div id="bottom-side" class="col-md-12">
		<div id="console" class="col-md-9 panel panel-default">
			<div class="panel-body">
				Console Lorem ipsum dolor sit amet, consectetur adipisicing elit. Fugiat, a, itaque provident similique quisquam dolorum earum perferendis est dolore perspiciatis alias animi officiis architecto eligendi dicta suscipit voluptas dolores illo! Lorem ipsum dolor sit amet, consectetur adipisicing elit. Fugiat, a, itaque provident similique quisquam dolorum earum perferendis est dolore perspiciatis alias animi officiis architecto eligendi dicta suscipit voluptas dolores illo! Lorem ipsum dolor sit amet, consectetur adipisicing elit. Fugiat, a, itaque provident similique quisquam dolorum earum perferendis est dolore perspiciatis alias animi officiis architecto eligendi dicta suscipit voluptas dolores illo! Lorem ipsum dolor sit amet, consectetur adipisicing elit. Fugiat, a, itaque provident similique quisquam dolorum earum perferendis est dolore perspiciatis alias animi officiis architecto eligendi dicta suscipit voluptas dolores illo! Lorem ipsum dolor sit amet, consectetur adipisicing elit. Fugiat, a, itaque provident similique quisquam dolorum earum perferendis est dolore perspiciatis alias animi officiis architecto eligendi dicta suscipit voluptas dolores illo! Lorem ipsum dolor sit amet, consectetur adipisicing elit. Fugiat, a, itaque provident similique quisquam dolorum earum perferendis est dolore perspiciatis alias animi officiis architecto eligendi dicta suscipit voluptas dolores illo! Lorem ipsum dolor sit amet, consectetur adipisicing elit. Fugiat, a, itaque provident similique quisquam dolorum earum perferendis est dolore perspiciatis alias animi officiis architecto eligendi dicta suscipit voluptas dolores illo! Lorem ipsum dolor sit amet, consectetur adipisicing elit. Fugiat, a, itaque provident similique quisquam dolorum earum perferendis est dolore perspiciatis alias animi officiis architecto eligendi dicta suscipit voluptas dolores illo!
			</div>
		</div>
		<div id="inspector" class="col-md-3 panel panel-default">
			<div class="panel-body">
				Inspector Lorem ipsum dolor sit amet, consectetur adipisicing elit. Fugiat, a, itaque provident similique quisquam dolorum earum perferendis est dolore perspiciatis alias animi officiis architecto eligendi dicta suscipit voluptas dolores illo! Lorem ipsum dolor sit amet, consectetur adipisicing elit. Fugiat, a, itaque provident similique quisquam dolorum earum perferendis est dolore perspiciatis alias animi officiis architecto eligendi dicta suscipit voluptas dolores illo! Lorem ipsum dolor sit amet, consectetur adipisicing elit. Fugiat, a, itaque provident similique quisquam dolorum earum perferendis est dolore perspiciatis alias animi officiis architecto eligendi dicta suscipit voluptas dolores illo! Lorem ipsum dolor sit amet, consectetur adipisicing elit. Fugiat, a, itaque provident similique quisquam dolorum earum perferendis est dolore perspiciatis alias animi officiis architecto eligendi dicta suscipit voluptas dolores illo! Lorem ipsum dolor sit amet, consectetur adipisicing elit. Fugiat, a, itaque provident similique quisquam dolorum earum perferendis est dolore perspiciatis alias animi officiis architecto eligendi dicta suscipit voluptas dolores illo! Lorem ipsum dolor sit amet, consectetur adipisicing elit. Fugiat, a, itaque provident similique quisquam dolorum earum perferendis est dolore perspiciatis alias animi officiis architecto eligendi dicta suscipit voluptas dolores illo! Lorem ipsum dolor sit amet, consectetur adipisicing elit. Fugiat, a, itaque provident similique quisquam dolorum earum perferendis est dolore perspiciatis alias animi officiis architecto eligendi dicta suscipit voluptas dolores illo!
			</div>
		</div>
	</div>
</div>

</body>
</html>