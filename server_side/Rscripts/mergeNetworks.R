#!/usr/bin/env Rscript

options(echo=TRUE)
args <- commandArgs(trailingOnly = TRUE)

# Check parameters
if(length(args) != 2) stop('./convertToJSON.R session_id config_file')

# Load requirements
library(igraph)
library(rjson)

source('./Graph_Manager.class.R')
nm <- GraphManager()

# Start
if(file.exists(paste0('../session/', args[1], '/'))) {
	setwd(paste0('../session/', args[1], '/'))
	
	if(file.exists(paste0(args[2], '.json'))) {

		cat('> Read config file\n')
		s <- read.delim(paste0(args[2], '.json'), header = F, as.is=T, quote = "")[1,1]
		l <- fromJSON(s)

		graph.list <- list()
		v.attr.table.list <- list()
		e.attr.table.list <- list()

		# For each selected network
		for (network in l$networks) {
			cat('> Work on graph "', network, '"\n')

			if ( file.exists(paste0(network, '.graphml')) ) {
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
					v.identity.list, 'tea_identity', '~')
				
				# Sort v.attr.table columns
				v.attr.table <- nm$sort.table.cols(v.attr.table)

				# EDGES #
				
				cat('\t- Edges\n')

				# Add extremities
				e.attr.table <- nm$add.edges.extremities(e.attr.table, g, F)

				# Convert edge extremities to v.identity
				e.attr.table <- nm$convert.extremities.to.v.identity(e.attr.table, v.attr.table,
					'tea_identity', g)

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
					e.identity.list, 'tea_identity', '~')

				# Sort edge attribute table
				e.attr.table <- nm$sort.table.cols(e.attr.table)

				# MAKE LISTS #
				v.attr.table.list <- nm$append.to.table.list(v.attr.table.list, v.attr.table)
				e.attr.table.list <- nm$append.to.table.list(e.attr.table.list, e.attr.table)
				graph.list <- append(graph.list, g)
			}
		}

		# VERTICES #

		cat('> Merging Vertices\n')

		# Merge tables from table.list
		v.attr.table.merged <- nm$merge.tables.from.table.list(v.attr.table.list)

		# Apply behavior
		v.attr.table.shrink <- nm$apply.fun.based.on.identity(v.attr.table.merged,
			'tea_identity', l$n_behavior, l$n_count_attr, 'merge_count')

		# Update IDs
		v.attr.table <- nm$update.row.ids(v.attr.table.shrink)
		v.attr.table <- nm$add.prefix.to.col(v.attr.table, 'id', 'n')

		# EDGES #

		cat('> Merging Edges\n')
		# Merge table from table.list
		e.attr.table.merged <- nm$merge.tables.from.table.list(e.attr.table.list)

		# Apply behavior
		e.attr.table.shrink <- nm$apply.fun.based.on.identity(e.attr.table.merged,
			'tea_identity', l$e_behavior, l$e_count_attr, 'merge_count')

		# Convert extremities to IDs
		e.attr.table <- nm$convert.extremities.to.v.id.based.on.table(e.attr.table.shrink,
			v.attr.table, 'tea_identity')

		# Updated IDs
		e.attr.table <- nm$update.row.ids(e.attr.table)
		e.attr.table <- nm$add.prefix.to.col(e.attr.table, 'id', 'e')

		# CONCLUSION #
		
		cat('> Output\n')

		# Remove identity columns
		if ( 'name' %in% nm$get.col.names(v.attr.table) ) {
			v.attr.table <- nm$rename.col(v.attr.table, 'name', 'name.bak')
			v.attr.table <- nm$rename.col(v.attr.table, 'tea_identity', 'name')
		} else {
			v.attr.table <- nm$rename.col(v.attr.table, 'tea_identity', 'name')
		}
		e.attr.table <- nm$rm.cols(e.attr.table, 'tea_identity')
		
		# Write GraphML
		g <- nm$attr.tables.to.graph(v.attr.table, e.attr.table)
		if ( 'grid' == l$default_layout) {
			coords <- layout.grid(g)*1000
		} else if ( 'circle' == l$default_layout ) {
			coords <- layout.circle(g)*1000
		}
		V(g)$x <- round(coords[,1], 0)
		V(g)$y <- round(coords[,2], 0)
		write.graph(g, paste0(l$new_name, '.graphml'), format='graphml')

		cat('Writing JSON file.\n')
		attr.tables <- nm$graph.to.attr.table(g)
		v.attr.table <- attr.tables$nodes
		e.attr.table <- attr.tables$edges
		v.attr.table <- nm$update.row.ids(v.attr.table)
		v.attr.table <- nm$add.prefix.to.col(v.attr.table, 'id', 'n')
		e.attr.table <- nm$convert.extremities.to.v.id.based.on.table(e.attr.table, v.attr.table, 'name')
		e.attr.table <- nm$update.row.ids(e.attr.table)
		e.attr.table <- nm$add.prefix.to.col(e.attr.table, 'id', 'e')
		graph.list <- nm$attr.tables.to.list(v.attr.table, e.attr.table)
		write(toJSON(graph.list), paste0(l$new_name, '.json'))

		if (file.exists(paste0(l$new_name, '.json'))) {
			cat('Preparing config file.\n')
			dat <- list(
				e_attributes=list.edge.attributes(g),
				e_count=ecount(g),
				v_attributes=list.vertex.attributes(g),
				v_count=vcount(g)
			)

			cat('Writing DAT file.\n')		
			write(toJSON(dat), paste0(l$new_name, '.dat'))
		}
		cat('~ END ~')
	}
}