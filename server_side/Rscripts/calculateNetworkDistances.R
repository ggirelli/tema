#!/usr/bin/env Rscript

options(echo=TRUE)
args <- commandArgs(trailingOnly = TRUE)

# Check parameters
if(length(args) != 2) stop('./convertToJSON.R session_id config_file')

# Load requirements
library(igraph)
library(rjson)

source('NetworkManager.class.R')
nm <- NetworkManager()

# Start
if(file.exists(paste0('/home/gire/public_html/SOGIv020/server_side/session/', args[1], '/'))) {
	setwd(paste0('/home/gire/public_html/SOGIv020/server_side/session/', args[1], '/'))
	
	if(file.exists(paste0(args[2], '.json'))) {

		cat('> Read config file\n')
		s <- scan(paste0(args[2], '.json'), 'raw')
		l <- fromJSON(s)

		graph.list <- list()
		v.attr.table.list <- list()
		e.attr.table.list <- list()

		# For each selected network
		for (network in l$networks) {
			cat('> Work on graph "', network, '"\n')

			# Read network
			g <- read.graph(paste0(network, '.graphml'), format='graphml')

			# Build attribute tables
			graph.list <- nm$graph.to.attr.table(g)
			v.attr.table <- graph.list$nodes
			e.attr.table <- graph.list$edges

			# VERTICES #

			cat('\t- Vertices\n')

			# Get attributes for vertex identity
			v.identity.list <- c()
			for (attr in names(l$n_identity)) {
				if (as.logical(l$n_identity[attr])) {
					v.identity.list <- append(v.identity.list, attr)
				}
			}

			# Expand with missing attributes
			v.attr.table <- nm$expand.attr.table(v.attr.table,
				c(v.identity.list, names(l$n_behavior)))
			
			# Add vertex identity column
			v.attr.table <- nm$add.collapsed.col(v.attr.table,
				v.identity.list, 'sogi_identity', '~')
			
			# Sort v.attr.table columns
			v.attr.table <- nm$sort.table.cols(v.attr.table)

			# EDGES #
			
			cat('\t- Edges\n')

			# Add extremities
			e.attr.table <- nm$add.edges.extremities(e.attr.table, g, F)

			# Convert edge extremities to v.identity
			e.attr.table <- nm$convert.extremities.to.v.identity(e.attr.table, v.attr.table,
				'sogi_identity', g)

			# Get attributes for edge identity
			e.identity.list <- c('source', 'target')
			for (attr in names(l$e.identity.list)) {
				if (as.logical(l$e.identity.list[attr])) {
					e.identity.list <- append(e.identity.list, attr)
				}
			}

			# Expand with missing attributes
			e.attr.table <- nm$expand.attr.table(e.attr.table,
				c(e.identity.list, names(l$e_behavior)))

			# Add edge identity column
			e.attr.table <- nm$add.collapsed.col(e.attr.table,
				e.identity.list, 'sogi_identity', '~')

			# Sort edge attribute table
			e.attr.table <- nm$sort.table.cols(e.attr.table)

			# MAKE LISTS #
			v.attr.table.list <- nm$append.to.table.list(v.attr.table.list, v.attr.table)
			e.attr.table.list <- nm$append.to.table.list(e.attr.table.list, e.attr.table)
			graph.list <- append(graph.list, g)
		}

		#print(v.attr.table.list)
		#print(length(v.attr.table.list))
		#print(e.attr.table.list)
		#print(length(e.attr.table.list))
		done.list <- c()
		for (i in 1:length(v.attr.table.list)) {
			for (j in 1:length(v.attr.table.list)) {
				if ( i != j ) {
					if ( !paste0(j, '_', i) %in% done.list ) {
						cat('Compare \'', l$networks[i], '\' with \'', l$networks[j], '\'\n')




						done.list <- append(done.list, paste0(i, '_', j))
					}
				}
			}
		}
	}
}