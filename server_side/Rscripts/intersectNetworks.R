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

		# VERTICES #

		cat('> Merging Vertices\n')

		# Merge tables from table.list
		v.attr.table.merged <- nm$merge.tables.from.table.list(v.attr.table.list)

		# Filter: remove those v.rows that do not appear length(l$networks) times
		v.identity.col.id <- which('sogi_identity' == colnames(v.attr.table.merged))
		v.identity.col <- v.attr.table.merged[, v.identity.col.id]
		if ( !is.null(nrow(v.attr.table)) ) {
			v.attr.table.merged <- v.attr.table.merged[which(
				v.identity.col %in% names(table(v.identity.col))[which(
					table(v.identity.col) == length(l$networks))]),]
		} else {
			v.attr.table.merged <- NULL
		}

		# Apply behavior
		v.attr.table.shrink <- nm$apply.fun.based.on.identity(v.attr.table.merged,
			'sogi_identity', l$n_behavior, F, 'merge_count')

		# Update IDs
		v.attr.table <- nm$update.row.ids(v.attr.table.shrink)
		v.attr.table <- nm$add.prefix.to.col(v.attr.table, 'id', 'n')

		# EDGES #

		cat('> Merging Edges\n')

		# Merge table from table.list
		e.attr.table.merged <- nm$merge.tables.from.table.list(e.attr.table.list)

		# Filter: remove those e.rows that do not appear length(l$networks) times
		e.identity.col.id <- which('sogi_identity' == colnames(e.attr.table.merged))
		e.identity.col <- e.attr.table.merged[, e.identity.col.id]
		if ( !is.null(nrow(e.attr.table)) ) {
			e.attr.table.merged <- e.attr.table.merged[which(
				e.identity.col %in% names(table(e.identity.col))[which(
					table(e.identity.col) == length(l$networks))]),]
		} else {
			e.attr.table.merged <- NULL
		}

		# Apply behavior
		e.attr.table.shrink <- nm$apply.fun.based.on.identity(e.attr.table.merged,
			'sogi_identity', l$e_behavior, F, 'merge_count')

		# Convert extremities to IDs
		e.attr.table <- nm$convert.extremities.to.v.id.based.on.table(e.attr.table.shrink,
			v.attr.table, 'sogi_identity')

		# Updated IDs
		e.attr.table <- nm$update.row.ids(e.attr.table)
		e.attr.table <- nm$add.prefix.to.col(e.attr.table, 'id', 'e')

		# CONCLUSION #
		
		cat('> Output\n')

		# Remove identity columns
		v.attr.table <- nm$rm.cols(v.attr.table, 'sogi_identity')
		e.attr.table <- nm$rm.cols(e.attr.table, 'sogi_identity')

		# Write GraphML
		g.out <- nm$attr.tables.to.graph(v.attr.table, e.attr.table)
		write.graph(g.out, paste0(l$new_name, '.graphml'), format='graphml')

		# Write graph DAT
		d <- list(
			e_attributes=list.edge.attributes(g.out),
			e_count=ecount(g.out), 
			v_attributes=list.vertex.attributes(g.out),
			v_count=vcount(g.out)
		)
		write(toJSON(d), paste0(l$new_name, '.dat'))

		# Write JSON
		graph.list <- nm$attr.tables.to.list(v.attr.table, e.attr.table)
		write(toJSON(graph.list), paste0(l$new_name, '.json'))

	}
}