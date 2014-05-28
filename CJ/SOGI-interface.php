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
$toInit = false;
if(count($uncommon) != 0) $toInit = true;

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

		/**
		 * Allows to add text to the console
		 * @param  {String} talk Text (html format) to add to the console inside a <p /> with the current timestamp
		 * @return {none}      
		 */
		function doConsole(talk) {
			var d = new Date();
			$('#console .panel-body').append($('<p />').html(d.getDate() + '-' + d.getMonth() + '-' + d.getFullYear() + ' ' + d.getHours() + ':' + d.getMinutes() + ':' + d.getSeconds() + ' ~ ' + talk));
			$('#console').scrollTop($('#console .panel-body').height());
		}

		/**
		 * Queries the server
		 * @param  {String} action  Query keyword
		 * @param  {Object} data    POST data required to perform the action
		 * @param  {function} success Function triggered if the client can contact the server
		 * @return {none}         
		 */
		function doServer(action, data, success) {
			$.ajax({
				method: 'POST',
				url: '<?php echo ROOT_URI; ?>doserve/' + action,
				data: data,
				success: function(data) {
					success(data);
				}
			});
		}

		/**
		 * Loads a graph in the canvas
		 * @param  {String} name graph name
		 * @return {none}
		 */
		function loadGraph(name) {
			doConsole('Loading graph "' + name + '" into canvas.');
			url = '<?php echo ROOT_URI; ?>session/<?php echo $id; ?>/' + name + '.json';
			$.getJSON(url, {}, function(data) {
				switch(data) {
					case 'E0': case 'E1': case 'E2': {
						doConsole('No connection, operation aborted.');
						break;
					}
					case 'E3': {
						doConsole('Only one operation at a time, thanks :)');
						break;
					}
					default: {
						$('#cy').cytoscape(function() {
							doConsole('Found ' + data['nodes'].length + ' nodes and ' + data['edges'].length + ' edges.');
							cy.load(data, function(e) {
								doConsole('Loaded.');
							});
						});
					}
				}
			});
		}

		/**
		 * Downloads a graph in JSON or graphml format
		 * @param  {String} name Graph's name
		 * @return {none}
		 */
		function downloadGraph(name) {
			$('.jumbotron').css({'display':'block'});
			$('.jumbotron .container').append($('<p />').html('To donwload the <i>\'' + name + '\'</i> graph,<br /><u>right click</u> on the desired format and select <b>save</b>:'));

			json_btn = $('<a />').text('json').addClass('btn btn-success btn-lg').attr('target','new').attr('href','<?php echo ROOT_URI; ?>session/<?php echo $id; ?>/' + name + '.json');
			graphml_btn = $('<a />').text('graphml').addClass('btn btn-warning btn-lg').attr('target','new').attr('href','<?php echo ROOT_URI; ?>session/<?php echo $id; ?>/' + name + '.graphml');

			$('.jumbotron .container').append(json_btn);
			$('.jumbotron .container').append('&nbsp;');
			$('.jumbotron .container').append(graphml_btn);

			$('.jumbotron .container').append($('<p />').html('or go <a href="javascript:hideJumbo()">back</a>...'));
		}

		/**
		 * Hides the jumbotron
		 * @return {none}
		 */
		function hideJumbo() {
			$('.jumbotron .container').html('');
			$('.jumbotron').css({'display':'none'});
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
					doServer('convertToJSON', {'name': this, 'id': '<?php echo $id; ?>'}, function(x) {
						alert(x);
					});
				});
			}

			// ----------------
			// CYTOSCAPE CANVAS
			// ----------------

			$('#cy').cytoscape({
				container: document.getElementById('cy'),
				maxZoom: 5,
				hideEdgesOnViewport: true,
				hideLabelsOnViewport: true,
				textureOnViewport: true,

				style: cytoscape.stylesheet()
					.selector('node').css({
						'background-color': 'white',
						'border-color': '#909090',
						'border-width': '1px',
						'content': 'data(name)',
						'text-valign': 'center',
						'color': '#323232',
						'min-zoomed-font-size': '10px',
						'font-family': 'arial',
						'text-outline-color': 'white',
						'text-outline-width': '1'
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
					  { data: { id: 'j', name: 'Welcome', weight: 65, height: 174 } },
					  { data: { id: 'e', name: 'to', weight: 48, height: 160 } },
					  { data: { id: 'k', name: 'SOGI', weight: 75, height: 185 } },
					],

					edges: [
					  { data: { source: 'j', target: 'e' } },
					  { data: { source: 'e', target: 'k' } },
					  { data: { source: 'k', target: 'e' } },
					],
				},

				layout: {
					name: 'grid',
					refresh: 0,
					fit: true,
					ready: function(){
						window.cy = this;

						// giddy up...

						//cy.elements().unselectify();

						cy.on('tap', 'node', function(e){
							var node = e.cyTarget; 
							var neighborhood = node.neighborhood().add(node);

							cy.elements().addClass('faded');
							neighborhood.removeClass('faded');

							$('#inspector .panel-body').html('');
							$('#inspector .panel-body').append($('<h5 />').html('<b>Inspecting node \'' + node.data('name') + '\'</b>'));
							$('#inspector .panel-body').append($('<div />').attr('id', 'attributes'));
							for(var k in node.data()) {
								if(k != 'name') {
									$('#inspector .panel-body #attributes').append($('<span />').html('<u>' + k + '</u> = ' + node.data(k) + '<br />'));
								}
							}
						});

						cy.on('tap', 'edge', function(e){
							var edge = e.cyTarget;

							$('#inspector .panel-body').html('');

							cy.elements().addClass('faded');
							edge.source().removeClass('faded');
							edge.target().removeClass('faded');

							$('#inspector .panel-body').append($('<h5 />').html('<b>Inspecting edge \'' + edge.data('id') + '\'</b>'));
							$('#inspector .panel-body').append($('<div />').attr('id', 'attributes'));
							for(var k in edge.data()) {
								if(k != 'id') {
									$('#inspector .panel-body #attributes').append($('<span />').html('<u>' + k + '</u> = ' + edge.data(k) + '<br />'));
								}
							}
						});

						cy.on('tap', function(e){
							if( e.cyTarget === cy ){
								$('#inspector .panel-body').html('');
								cy.elements().removeClass('faded');
							}
						});
					},
					stop: function() {
						cy.center(cy.$('*'));
					}
				}
			});

			// ------------
			// COMMAND LINE
			// ------------
			
			$('#cmd-line').submit(function(e) {
				e.preventDefault();
				alert(1);
			});

		});
	</script>
</head>
<body>

<!-- SOGI, session <?php echo $id; ?> -->

<div class="jumbotron"><div class="container"></div></div>


<div id='left-side' class="col-md-2 panel-group">
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h4 class="panel-title"><a href="#graph-list" data-toggle="collapse" data-parent="#left-side">Graph List</a></h4>
		</div>
		<div class="panel-collapse collapse in" id="graph-list">
			<div class="panel-body">
				<?php
				foreach($ss->getJSONFileList() as $fname) {
					$s = '<a href="javascript:loadGraph(\'' . $fname . '\')" class="col-md-8">' . $fname . '</a>';
					$s .= '<div class="col-md-4">';
					$s .= '<a href="javascript:downloadGraph(\'' . $fname . '\')"><span class="glyphicon glyphicon glyphicon-cloud-download"></span></a>&nbsp;&nbsp;';
					$s .= '<a href=""><span class="glyphicon glyphicon-pencil"></span></a>&nbsp;&nbsp;';
					$s .= '<a href=""><span class="glyphicon glyphicon-remove"></span></a>';
					$s .= '</div>';
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
				...
			</div>
		</div>
	</div>
	<div class="panel panel-info">
		<div class="panel-heading">
			<h4 class="panel-title"><a href="#graph-settings" data-toggle="collapse" data-parent="#left-side">Settings</a></h4>
		</div>
		<div class="panel-collapse collapse" id="graph-settings">
			<div class="panel-body">
				...
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
	<h1 id="interface-title">SOGI</h1>
	<div id="canvas" class="col-md-12">
		<div id="cy"></div>
	</div>
	<div id="bottom-side" class="col-md-12">
		<div id="console" class="col-md-9 panel panel-default">
			<div class="panel-body">
				
			</div>
			<form id='cmd-line' class='form-inline'>
				<div class="col col-md-11">
					<input type='text' class='form-control' placeholder='SOGI command line' />
				</div>
				<div class="col col-md-1">
					<button class='btn btn-info btn-block'><span class="glyphicon glyphicon-forward"></span></button>
				</div>
			</form>
		</div>
		<div id="inspector" class="col-md-3 panel panel-default">
			<div class="panel-body">
				
			</div>
		</div>
	</div>
</div>

</body>
</html>