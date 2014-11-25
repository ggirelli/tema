#!/usr/bin/env Rscript

options(echo=TRUE)
args <- commandArgs(trailingOnly = TRUE)

# Check parameters
if(length(args) != 4) stop('./addIndexAttributeToNetwork.R session_id graph_name attr_name attr_index')

# Load requirements
library(igraph)
library(rjson)

source('./NetworkManager.class.R')
nm <- NetworkManager()

# Start
if(file.exists(paste0('../session/', args[1], '/'))) {
	setwd(paste0('../session/', args[1], '/'))

	cat('> Read JSON file\n')
	s <- read.delim(paste0(args[2], '.json'), header = F, as.is=T, quote = "")[1,1]
	l <- fromJSON(s)

	g <- nm$graph.list.to.graph(l)

	cat('> Add attribute\n')
	if( 'degree' == args[4] ) {
		ind <- degree(g, V(g))
	} else if( 'indegree' == args[4] ) {
		ind <- degree(g, V(g), mode='in')
	} else if( 'outdegree' == args[4] ) {
		ind <- degree(g, V(g), mode='out')
	} else if ( 'betweenness' == args[4] ) {
		ind <- betweenness(g, V(g))
	} else if ( 'closeness' == args[4] ) {
		ind <- closeness(g, V(g))
	}
	eval(parse(text=paste0('V(g)$', args[3], ' <- ind')))

	graph.list <- nm$graph.to.attr.table(g)
	graph.list$nodes <- nm$update.row.ids(graph.list$nodes)
	graph.list$nodes <- nm$add.prefix.to.col(graph.list$nodes, 'id', 'n')
	graph.list$edges <- nm$convert.extremities.to.v.id.based.on.table(graph.list$edges,
		graph.list$nodes, 'name')
	graph.list$edges <- nm$update.row.ids(graph.list$edges)
	graph.list$edges <- nm$add.prefix.to.col(graph.list$edges, 'id', 'e')
	write(toJSON(nm$attr.tables.to.list(graph.list$nodes, graph.list$edges)),
		paste0(args[2], '.json'))
}