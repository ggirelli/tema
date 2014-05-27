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


# Compare graphml/JSON lists
$uncommon = $ss->getToConvertFileList();
if(count($uncommon) != 0) $toInit = TRUE;

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
		var toInit = <?php if($toInit) { echo 1; } else { echo 0; } ?>;

		function doServer(action, data, success) {
			$.ajax({
				url: '<?php echo ROOT_URI; ?>a/' + action,
				data: data,
				success: success
			});
		}

		$(document).ready(function() {

			// ----------
			// INITIALIZE
			// ----------

			if(toInit) {
				$('#console .panel-body').append($('<p />').text('Initializing the interface...'));
				$('#console .panel-body').append($('<p />').text('I am going to convert some files into the JSON format:'));
				var uncommon = <?php echo '["' . implode('", "', $uncommon) . '"]'; ?>;
				$(uncommon).each(function() {
					$('#console .panel-body').append($('<p />').text('Converting ').append($('<span />').text(this).css({'text-decoration':'underline'})));
					$()
				});
			}

			// ----------------
			// CYTOSCAPE CANVAS
			// ----------------

			$('#cy').cytoscape({
				container: document.getElementById('cy'),
				minZoom: 1,
				maxZoom: 5,
				hideEdgesOnViewport: true,
				hideLabelsOnViewport: true,
				textureOnViewport: true,

				style: cytoscape.stylesheet()
					.selector('node').css({
						'content': 'data(name)',
						'text-valign': 'center',
						'color': 'white',
						'min-zoomed-font-size': '10px',
						'text-outline-width': 2,
						'text-outline-color': '#888'
					})
					.selector('edge').css({
						'target-arrow-shape': 'triangle'
					})
					.selector(':selected').css({
						'background-color': 'black',
						'line-color': 'black',
						'target-arrow-color': 'black',
						'source-arrow-color': 'black'
					})
					.selector('.faded').css({
						'opacity': 0.25,
						'text-opacity': 0
					}),

				elements: {
					nodes: [
					  { data: { id: 'j', name: 'Jerry', weight: 65, height: 174 } },
					  { data: { id: 'e', name: 'Elaine', weight: 48, height: 160 } },
					  { data: { id: 'k', name: 'Kramer', weight: 75, height: 185 } },
					  { data: { id: 'g', name: 'George', weight: 70, height: 150 } }
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
					],
				},

				layout: {
					name: 'cose',
					refresh: 0,
					fit: true,
					padding: 30,
					randomize: true,
					debug: false,
					nodeRepulsion: 10000,
					nodeOverlap: 10,
					idealEdgeLength: 10,
					edgeElasticity: 100,
					nestingFactor: 5,
					gravity: 250,
					numIter: 100,
					initialTemp: 200,
					coolingFactor: 0.95,
					minTemp: 1,
					ready: function(){
						window.cy = this;

						// giddy up...

						//cy.elements().unselectify();

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
					},
					stop: function() {
						cy.center(cy.$('*'));
					}
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
		<div class="panel-collapse collapse in" id="graph-list">
			<div class="panel-body">
				<?php
				foreach($ss->getJSONFileList() as $fname) {
					$s = "<a href='' class='col-md-8'>$fname</a>";
					$s .= "<div class='col-md-4'>";
					$s .= "<a href=''><span class='glyphicon glyphicon-remove'></span></a>&nbsp;&nbsp;";
					$s .= "<a href=''><span class='glyphicon glyphicon glyphicon-cloud-download'></span></a>";
					$s .= "</div>";
					echo $s;
				}
				?>
				<a id='upload-new' href=''><span class="glyphicon glyphicon-cloud-upload"></span></a>
			</div>
		</div>
	</div>
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h4 class="panel-title"><a href="#graph-tools" data-toggle="collapse" data-parent="#left-side">Graph Tools</a></h4>
		</div>
		<div class="panel-collapse collapse" id="graph-tools">
			<div class="panel-body">
				<button type="button" class="btn btn-warning btn-md">
					<span class="glyphicon glyphicon-link"></span>
				</button>
				<button type="button" class="btn btn-warning btn-md">
					<span class="glyphicon glyphicon-resize-small"></span>
				</button>
				<button type="button" class="btn btn-warning btn-md">
					<span class="glyphicon glyphicon-resize-full"></span>
				</button>
				<button type="button" class="btn btn-warning btn-md">
					<span class="glyphicon glyphicon-record"></span>
				</button>
				<button type="button" class="btn btn-warning btn-md" onclick="javascript:$('#cy').cytoscape(function(){cy.center(cy.$('*'))});">
					<span class="glyphicon glyphicon-screenshot"></span>
				</button>
			</div>
		</div>
	</div>
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h4 class="panel-title"><a href="#graph-style" data-toggle="collapse" data-parent="#left-side">Graph Style</a></h4>
		</div>
		<div class="panel-collapse collapse" id="graph-style">
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