#!/usr/bin/env Rscript

#options(echo=FALSE, warn=-1)
args <- commandArgs(trailingOnly = TRUE)

# Check parameters
if(length(args) != 5) stop('./getNeighborhood.R session_id network_name node_id node_thr mode')

# Load requirements
library(igraph)
library(rjson)

source('NetworkManager.class.R')
nm <- NetworkManager()

# Start
if(file.exists(paste0('../session/', args[1], '/'))) {
	setwd(paste0('../session/', args[1], '/'))
	
	g <- read.graph(paste0(args[2], '.graphml'), format='graphml')
	E(g)$id <- paste0('e', 0:(ecount(g) - 1))
	
	size <- neighborhood.size(g, 1, V(g)[id == args[3]], mode=args[5])
	order <- 2
	tmp <- neighborhood.size(g, order, V(g)[id == args[3]], mode=args[5])

	while ( tmp <= args[4] && tmp > size ) {
		size <- tmp
		order <- order + 1
		tmp <- neighborhood.size(g, order, V(g)[id == args[3]], mode=args[5])
	}

	order <- order - 1

	n <- graph.neighborhood(g, order, V(g)[id == args[3]], mode=args[5])[[1]]

	write(toJSON(list(nodes=V(n)$id, edges=E(n)$id)), 'tmp_r_output')
}
