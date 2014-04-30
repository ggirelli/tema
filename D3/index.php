<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>SOGI - Simple Online Graph Interface</title>
	<script src="js/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/d3.min.js"></script>

    <script type="text/javascript" src="js/json2.min.js"></script>
    <script type="text/javascript" src="js/AC_OETags.min.js"></script>
    <script type="text/javascript" src="js/cytoscapeweb.min.js"></script>

	<link rel="stylesheet"  type="text/css" href="css/bootstrap.css" />
	<link rel="stylesheet"  type="text/css" href="css/main.css" />

	<script type="text/javascript">

	$(document).ready(function() {

var w = 1200,
    h = 700,
    r = d3.scale.sqrt().domain([0, 20000]).range([0, 20]);

var force = d3.layout.force()
    .gravity(.01)
    .charge(-120)
    .linkDistance(60)
    .size([w, h]);

var svg = d3.select("#canvas").append("svg:svg")
    .attr("width", w)
    .attr("height", h);

d3.xml("small.graphml", "application/xml", function(xml) {
  var nodes = self.nodes = d3.select(xml).selectAll("node")[0],
      links = self.links = d3.select(xml).selectAll("edge")[0].map(function(e) {
	      	return {source: $(nodes).filter('[id="' + e.attributes[0].value + '"]')[0], target: $(nodes).filter('[id="' + e.attributes[1].value + '"]')[0]};
	      });
      console.log(links)
      
  force
      .nodes(nodes)
      .links(links)
      .start();
  
  var link = svg.selectAll("line.link")
      .data(links)
    .enter().append("svg:line")
      .attr("class", "link")
      .attr("x1", function(d) { return d.source.x; })
      .attr("y1", function(d) { return d.source.y; })
      .attr("x2", function(d) { return d.target.x; })
      .attr("y2", function(d) { return d.target.y; });
  
  var node = svg.selectAll("circle.node")
      .data(nodes)
    .enter().append("svg:circle")
      .attr("class", "node")
      .attr("cx", function(d) { return d.x; })
      .attr("cy", function(d) { return d.y; })
      .attr("r", function(d) { return r(d.textContent) || 5; })
      .call(force.drag);
  
  force.on("tick", function() {
    nodes[0].x = w / 2;
    nodes[0].y = h / 2;

    link.attr("x1", function(d) { return d.source.x; })
        .attr("y1", function(d) { return d.source.y; })
        .attr("x2", function(d) { return d.target.x; })
        .attr("y2", function(d) { return d.target.y; });
  
    node.attr("cx", function(d) { return d.x; })
        .attr("cy", function(d) { return d.y; });
  });
});

	});

	</script>

</head>
<body>

<div id="canvas"></div>

</body>
</html>