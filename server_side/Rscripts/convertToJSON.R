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
	
	if(file.exists(paste0(args[2], '.graphml'))) {
		cat('Reading GRAPHML file.\n')
		g <- read.graph(paste0(args[2], '.graphml'), format='graphml')
		if ( 'grid' == args[3]) {
			coords <- layout.grid(g)*1000
		} else if ( 'circle' == args[3] ) {
			coords <- layout.circle(g)*1000
		}
		V(g)$x <- round(coords[,1], 0)
		V(g)$y <- round(coords[,2], 0)
		write.graph(g, paste0(args[2], '.graphml'), format='graphml')

		cat('Writing JSON file.\n')
		graph.list <- nm$graph.to.attr.table(g)
		graph.list$nodes <- nm$update.row.ids(graph.list$nodes)
		graph.list$nodes <- nm$add.prefix.to.col(graph.list$nodes, 'id', 'n')
		graph.list$edges <- nm$convert.extremities.to.v.id.based.on.table(graph.list$edges,
			graph.list$nodes, 'name')
		graph.list$edges <- nm$update.row.ids(graph.list$edges)
		graph.list$edges <- nm$add.prefix.to.col(graph.list$edges, 'id', 'e')
		write(toJSON(nm$attr.tables.to.list(graph.list$nodes, graph.list$edges)),
			paste0(args[2], '.json'))

		if (file.exists(paste0(args[2], '.json'))) {
			cat('Preparing config file.\n')
			l <- list(
				e_attributes=list.edge.attributes(g),
				e_count=ecount(g),
				v_attributes=list.vertex.attributes(g),
				v_count=vcount(g)
			)

			cat('Writing DAT file.\n')		
			write(toJSON(l), paste0(args[2], '.dat'))
		}

		cat('Converted.\n')
	}
}