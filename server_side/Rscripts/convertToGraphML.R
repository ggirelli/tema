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
if(file.exists(paste0('/home/gire/public_html/SOGIv020/server_side/session/', args[1], '/'))) {
	setwd(paste0('/home/gire/public_html/SOGIv020/server_side/session/', args[1], '/'))

	cat('> Read JSON file\n')
	s <- scan(paste0(args[2], '.json'), 'raw')
	l <- fromJSON(s)

	# From list to attr.tables
	attr.tables <- nm$graph.list.to.attr.tables(l)

	cat('> Convert to GraphML\n')
	g <- nm$attr.tables.to.graph(attr.tables$nodes, attr.tables$edges)
	write.graph(g, paste0(args[2], '.graphml'), format='graphml')

	cat('> Write DAT file\n')
	l <- list(e_attributes=list.edge.attributes(g), e_count=ecount(g), v_attributes=list.vertex.attributes(g), v_count=vcount(g))
	write(toJSON(l), paste0(args[2], '.dat'))

}