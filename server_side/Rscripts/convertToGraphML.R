#!/usr/bin/env Rscript

options(echo=TRUE)
args <- commandArgs(trailingOnly = TRUE)

# Check parameters
if(length(args) != 3) stop('./convertToJSON.R session_id graph_name layout')

# Load requirements
library(igraph)
library(rjson)

source('NetworkManager.class.R')
nm <- NetworkManager()

# Start
if(file.exists(paste0('../session/', args[1], '/'))) {
	setwd(paste0('../session/', args[1], '/'))

	cat('> Read JSON file\n')
	s <- read.delim(paste0(args[2], '.json'), header = F, as.is=T, quote = "")[1,1]
	l <- fromJSON(s)

	cat('> Convert to GraphML\n')
	g <- nm$graph.list.to.graph(l)
	if ( 'grid' == args[3]) {
		coords <- layout.grid(g)*1000
	} else if ( 'circle' == args[3] ) {
		coords <- layout.circle(g)*1000
	}
	V(g)$x <- round(coords[,1], 0)
	V(g)$y <- round(coords[,2], 0)
	write.graph(g, paste0(args[2], '.graphml'), format='graphml')

	cat('> Write DAT file\n')
	l <- list(
		e_attributes=list.edge.attributes(g),
		e_count=ecount(g),
		v_attributes=list.vertex.attributes(g),
		v_count=vcount(g)
	)
	write(toJSON(l), paste0(args[2], '.dat'))

}