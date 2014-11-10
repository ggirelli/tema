#!/usr/bin/env Rscript

options(echo=TRUE)
args <- commandArgs(trailingOnly = TRUE)

# Check parameters
if(length(args) != 2) stop('./convertToJSON.R session_id graph_name')

# Load requirements
library(igraph)
library(rjson)
source('extendIgraph.R')

# Start
if(file.exists(paste0('/home/gire/public_html/SOGIv020/server_side/session/', args[1], '/'))) {
	setwd(paste0('/home/gire/public_html/SOGIv020/server_side/session/', args[1], '/'))
	
	if(file.exists(paste0(args[2], '.graphml'))) {
		cat('Reading GRAPHML file.\n')
		g <- read.graph(paste0(args[2], '.graphml'), format='graphml')

		cat('Preparing config file.\n')
		l <- list(e_attributes=list.edge.attributes(g), e_count=ecount(g), v_attributes=list.vertex.attributes(g), v_count=vcount(g))
		
		write(toJSON(l), paste0(args[2], '.dat'))

		cat('Writing JSON file.\n')
		write.graph(g, paste0(args[2], '.json'), format='json')

		cat('Converted.\n')
	}
}