#!/usr/bin/env Rscript

options(echo=TRUE)
args <- commandArgs(trailingOnly = TRUE)

# Check parameters
if(length(args) != 2) stop('./convertToJSON.R session_id graph_name')

# Load requirements
library(igraph)
library(rjson)

source('NetworkManager.class.R')
nm <- NetworkManager()

# Start
if(file.exists(paste0('../session/', args[1], '/'))) {
	setwd(paste0('../session/', args[1], '/'))

	cat('> Read JSON file\n')
	s <- read.delim(paste0(args[2], '.json'))
	print(as.character(s))
	write(s, 'aaaaaa.txt')
	l <- fromJSON(s)

	cat('> Convert to GraphML\n')
	g <- nm$graph.list.to.graph(l)
	write.graph(g, paste0(args[2], '.graphml'), format='graphml')
	write.graph(g, paste0(args[2], '.gml'), format='gml')

	cat('> Write DAT file\n')
	l <- list(e_attributes=list.edge.attributes(g), e_count=ecount(g), v_attributes=list.vertex.attributes(g), v_count=vcount(g))
	write(toJSON(l), paste0(args[2], '.dat'))

}