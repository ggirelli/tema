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
			talk = d.getFullYear() + '-' + d.getMonth() + '-' + d.getDate() + ' ' + d.getHours() + ':' + d.getMinutes() + ':' + d.getSeconds() + ' ~ ' + talk;
			doServer('doConsole', {'text':'<p>' + talk + '</p>', 'id':'<?php echo $id; ?>'}, function(data) {
				$('#console .panel-body .wrapper').append($('<p />').html(talk));
				$('#console .panel-body').scrollTop($('#console .panel-body .wrapper').height());
			});
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
				},
				fail: function() {
					doConsole('<u>Triggered error</u>.');
				}
			});
		}

		/**
		 * Loads a graph in the canvas
		 * @param  {String} name graph name
		 * @return {none}
		 */
		function loadGraph(name) {
			doServer('loadGraph', {'name':name,'id':'<?php echo $id; ?>'}, function(data) {
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
						doConsole('Loading graph "' + name + '" into canvas.');
						data = $.parseJSON(data);
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
		 * Manages convertion of a list of graphs from graphml to JSON
		 * @param  {Array} lnames List of graph names
		 * @return {none}        Writes directly to consoles
		 */
		function convertGraphs(lnames, index) {
			doConsole('Converting <i>\'' + lnames[index] + '\'</i> to JSON format.');
			doServer('convertToJSON', {'name':lnames[index], 'id':'<?php echo $id; ?>'}, function(data) {
				switch(data) {
					case 'E0': case 'E1': case 'E2': {
						doConsole('Mr. Server isn\'t answering.');
						break;
					}
					case 'E3': {
						doConsole('Only one operation at a time, please...');
						break;
					}
					default: {
						doConsole('<i>\'' + lnames[index] + '\'</i> converted.');

						$('#graph-list .panel-body').append('<a href="javascript:loadGraph(\'' + lnames[index] + '\')" class="col-md-8" data-gname="' + lnames[index] + '">' + lnames[index] + '</a><div class="col-md-4" data-gname="' + lnames[index] + '"><a href="javascript:downloadGraph(\'' + lnames[index] + '\')"><span class="glyphicon glyphicon glyphicon-cloud-download"></span></a>&nbsp;&nbsp;<a href="javascript:renameGraph(\'' + lnames[index] + '\')"><span class="glyphicon glyphicon-pencil"></span></a>&nbsp;&nbsp;<a href="javascript:removeGraph(\'' + lnames[index] + '\')"><span class="glyphicon glyphicon-remove"></span></a></div>');

						if((index+1) < lnames.length) {
							convertGraphs(lnames, index+1);
						}
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
			doConsole('Downloading graph <i>\'' + name + '\'</i>, choose the format:');
			doConsole('<a href="<?php echo ROOT_URI; ?>session/<?php echo $id; ?>/' + name + '.json" target="_new">JSON</a> or <a href="<?php echo ROOT_URI; ?>session/<?php echo $id; ?>/' + name + '.graphml" target="_new">graphml</a>');
			$('#cmd-line').unbind('submit');
			$('#cmd-line').submit(function(e) {
				e.preventDefault();
				val = $('#cmd-line input[type=text]').val();
				doConsole(val);
				downloadGraphConsole(name,1);
			});
		}

		/**
		 * Renames a graph
		 * @param  {String} name Graph's old name
		 * @return {none}
		 */
		function renameGraph(name) {
			// Ask to insert new name
			doConsole('Insert the new name for graph <u>\'' + name + '\'</u>:');
			doConsole('<i>(allowed characters: a-z, A-Z, 0-9, _, -)</i>');

			$('#cmd-line input[type=text]').val(name).select();

			// Change command-line submit event
			$('#cmd-line').unbind('submit');
			$('#cmd-line').submit(function(e) {
				e.preventDefault();

				// Retrieve new name
				val = $('#cmd-line input[type=text]').val();
				doConsole(val);

				// Check new name
				if(/^([0-9a-zA-Z_-]*)$/.test(val)) {
					// Rename
					doServer('renameGraph', {'id':'<?php echo $id; ?>', 'old_name':name, 'new_name':val}, function(data) {
						switch(data) {
							case 'E0': case 'E1': {
								doConsole('An error occurred while contacting the server. Try again later.');
								break;
							}
							case 'E2': {
								doConsole('The server cannot accept empty parameter');
								break;
							}
							case 'E3': {
								doConsole('Cannot rename non-existent graph.');
								break;
							}
							case 'E4': {
								doConsole('A graph with the new name is already present, please try with a different one.');
								break;
							}
							case 'OK': {
								$('#graph-list .panel-body a[data-gname=' + name + ']').text(val).attr('href', 'javascript:loadGraph(\'' + val + '\')');
								$('#graph-list .panel-body a[data-gname=' + name + ']').attr('data-gname', val);
								$('#graph-list .panel-body div[data-gname=' + name + ']').replaceWith('<div class="col-md-4" data-gname="' + val + '"><a href="javascript:downloadGraph(\'' + val + '\',0)"><span class="glyphicon glyphicon glyphicon-cloud-download"></span></a>&nbsp;&nbsp;<a href="javascript:renameGraph(\'' + val + '\')"><span class="glyphicon glyphicon-pencil"></span></a>&nbsp;&nbsp;<a href=""><span class="glyphicon glyphicon-remove"></span></a></div>');
								doConsole('Renamed.');

								$('#cmd-line input[type=text]').val('');

								$('#cmd-line').unbind('submit');
								$('#cmd-line').submit(function(e) { e.preventDefault(); cmdSubmit(); });
								break;
							}
							default:
								alert(data);
						}
					});
				} else {
					// Ask for correct new name
					doConsole('Please, use only allowed characters. Try again:');
					doConsole('<i>(allowed characters: a-z, A-Z, 0-9, _, -)</i>');
				}
			});
		}

		/**
		 * Removes a graph
		 * @param  {String} name Graph's name
		 * @return {none}
		 */
		function removeGraph(name) {
			// Ask to insert new name
			doConsole('Do you really want to remove graph \'<u>' + name + '</u>\'? (y/n)');

			$('#cmd-line input[type=text]').val('').select();

			// Change command-line submit event
			$('#cmd-line').unbind('submit');
			$('#cmd-line').submit(function(e) {
				e.preventDefault();

				// Retrieve answer
				val = $('#cmd-line input[type=text]').val();
				doConsole(val);

				// Check new name
				if(/^[ynYN]$/.test(val)) {
					if(/^[yY]$/.test(val)) {
						// Remove
						doServer('removeGraph', {'id':'<?php echo $id; ?>', 'name':name}, function(data) {
							switch(data) {
								case 'E0': case 'E1': {
									doConsole('An error occurred while contacting the server. Try again later.');
									break;
								}
								case 'E1': {
									doConsole('Cannot remove non-existent graph.');
									break;
								}
								case 'OK': {
									$('#graph-list .panel-body a[data-gname=' + name + ']').remove();
									$('#graph-list .panel-body div[data-gname=' + name + ']').remove();
									doConsole('Removed graph \'<u>' + name + '</u>\'');

									$('#cmd-line input[type=text]').val('');

									$('#cmd-line').unbind('submit');
									$('#cmd-line').submit(function(e) { e.preventDefault(); cmdSubmit(); });
									break;
								}
								default:
									alert(data);
							}
						});
					} else {
						$('#cmd-line input[type=text]').val('');

						$('#cmd-line').unbind('submit');
						$('#cmd-line').submit(function(e) { e.preventDefault(); cmdSubmit(); });
					}
				} else {
					// Ask for correct new name
					$('#cmd-line input[type=text]').val('');
					doConsole('Please, answer correctly. (y/n)');
				}
			});
		}

		/**
		 * Loads the form to upload graphs into the current session
		 * @return {none}
		 */
		function uploadGraph() {
			$.ajax({
				url: '<?php echo ROOT_URI . 'include/HTMLremote/uploadGraph.form.php'; ?>',
				type: 'POST',
				data: {'id':'<?php echo $id; ?>'},
				success: function(data) {
					showJumbo(data);
				}
			});
		}

		/**
		 * Basic command line submit event
		 * @return {none}
		 */
		function cmdSubmit() {
			doConsole($('#cmd-line input[type=text]').val());
			$('#cmd-line input[type=text]').val('');
		}

		/**
		 * Checks if a query is running (back-end)
		 * @return {none} Shows query status
		 */
		function isRunning() {
			doServer('isRunning', {'id':'<?php echo $id; ?>'}, function(data) {
				if(data == 1) {
					console.log('running');
				} else if(data == 0) {
					console.log('not running');
				} else {
					console.lof('error');
				}
			})
		}

		/**
		 * Shows the jumbotron
		 * @attr {String} html [jumbotron html content]
		 * @return {none}
		 */
		function showJumbo(html) {
			$('.jumbotron .container').html(html);
			$('.jumbotron').css({'display':'block'});
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
			
			$('#console .panel-body').scrollTop($('#console .panel-body .wrapper').height());
			if(toInit) {
				doConsole('Initializing the interface...');
				doConsole('I am going to convert some files into the JSON format:');
				convertGraphs(<?php echo '["' . implode('", "', $uncommon) . '"]'; ?>, 0)
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
<?php if($ss->get('graph') == '0') { ?>
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
<?php } else { ?>
				elements: <?php echo file_get_contents(SESS_PATH . $id . '/' . $ss->get('graph') . '.json'); ?>,
<?php } ?>
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
			
			$('#cmd-line').submit(function(e) { e.preventDefault(); cmdSubmit(); });

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
					$s = '<a href="javascript:loadGraph(\'' . $fname . '\')" class="col-md-8" data-gname="' . $fname . '">' . $fname . '</a>';
					$s .= '<div class="col-md-4" data-gname="' . $fname . '">';
					$s .= '<a href="javascript:downloadGraph(\'' . $fname . '\',0)"><span class="glyphicon glyphicon glyphicon-cloud-download"></span></a>&nbsp;&nbsp;';
					$s .= '<a href="javascript:renameGraph(\'' . $fname . '\')"><span class="glyphicon glyphicon-pencil"></span></a>&nbsp;&nbsp;';
					$s .= '<a href="javascript:removeGraph(\'' . $fname . '\')"><span class="glyphicon glyphicon-remove"></span></a>';
					$s .= '</div>';
					echo $s;
				}
				?>
				<a id='upload-new' href='javascript:uploadGraph()'><span class="glyphicon glyphicon-cloud-upload"></span></a>
			</div>
		</div>
	</div>
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h4 class="panel-title"><a href="#graph-tools" data-toggle="collapse" data-parent="#left-side">Graph Tools</a></h4>
		</div>
		<div class="panel-collapse collapse" id="graph-tools">
			<div class="panel-body">
				<button type="button" class="btn btn-warning btn-md" onclick='javascript:'>
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
			<div class="panel-body"><div class="wrapper"><?php echo file_get_contents(SESS_PATH . $id . '/CONSOLE'); ?></div></div>
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