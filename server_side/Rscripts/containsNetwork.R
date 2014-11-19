#!/usr/bin/env Rscript

#options(echo=TRUE)
args <- commandArgs(trailingOnly = TRUE)

# Check parameters
if(length(args) != 2) stop('./convertToJSON.R session_id config_file')

# Load requirements
library(igraph)
library(rjson)

source('NetworkManager.class.R')
nm <- NetworkManager()

# Start
if(file.exists(paste0('../session/', args[1], '/'))) {
	setwd(paste0('../session/', args[1], '/'))
	
	if(file.exists(paste0(args[2], '.json'))) {

		s <- scan(paste0(args[2], '.json'), 'raw')
		l <- fromJSON(s)

		# SUPERNET #
	
		# Read network
		g <- read.graph(paste0(l$super, '.graphml'), format='graphml')

		# Build attribute tables
		graph.list <- nm$graph.to.attr.table(g)
		v.supernet.attr.table <- graph.list$nodes
		e.supernet.attr.table <- graph.list$edges

		# SUPERNET VERTICES #

		# Get attributes for vertex identity
		v.identity.list <- c()
		for (attr in names(l$n_identity)) {
			if (as.logical(l$n_identity[attr])) {
				v.identity.list <- append(v.identity.list, attr)
			}
		}

		# Expand with missing attributes
		v.supernet.attr.table <- nm$expand.attr.table(v.supernet.attr.table,
			c(v.identity.list, names(l$n_behavior)))

		# Add vertex identity column
		v.supernet.attr.table <- nm$add.collapsed.col(v.supernet.attr.table,
			v.identity.list, 'sogi_identity', '~')

		# Sort v.supernet.attr.table columns
		v.supernet.attr.table <- nm$sort.table.cols(v.supernet.attr.table)

		# SUPERNET EDGES #

		# Add extremities
		e.supernet.attr.table <- nm$add.edges.extremities(e.supernet.attr.table, g, F)

		# Convert edge extremities to v.identity
		e.supernet.attr.table <- nm$convert.extremities.to.v.identity(e.supernet.attr.table,
			v.supernet.attr.table, 'sogi_identity', g)

		# Get attributes for edge identity
		e.identity.list <- c('source', 'target')
		for (attr in names(l$e.identity.list)) {
			if (as.logical(l$e.identity.list[attr])) {
				e.identity.list <- append(e.identity.list, attr)
			}
		}

		# Expand with missing attributes
		e.supernet.attr.table <- nm$expand.attr.table(e.supernet.attr.table,
			c(e.identity.list, names(l$e_behavior)))

		# Add edge identity column
		e.supernet.attr.table <- nm$add.collapsed.col(e.supernet.attr.table,
			e.identity.list, 'sogi_identity', '~')

		# Sort edge attribute table
		e.supernet.attr.table <- nm$sort.table.cols(e.supernet.attr.table)

		# SUBNET #

		# Read network
		g <- read.graph(paste0(l$sub, '.graphml'), format='graphml')

		# Build attribute tables
		graph.list <- nm$graph.to.attr.table(g)
		v.subnet.attr.table <- graph.list$nodes
		e.subnet.attr.table <- graph.list$edges

		# SUBNET VERTICES #

		# Get attributes for vertex identity
		v.identity.list <- c()
		for (attr in names(l$n_identity)) {
			if (as.logical(l$n_identity[attr])) {
				v.identity.list <- append(v.identity.list, attr)
			}
		}

		# Expand with missing attributes
		v.subnet.attr.table <- nm$expand.attr.table(v.subnet.attr.table,
			c(v.identity.list, names(l$n_behavior)))

		# Add vertex identity column
		v.subnet.attr.table <- nm$add.collapsed.col(v.subnet.attr.table,
			v.identity.list, 'sogi_identity', '~')

		# Sort v.subnet.attr.table columns
		v.subnet.attr.table <- nm$sort.table.cols(v.subnet.attr.table)

		# SUBNET EDGES #

		# Add extremities
		e.subnet.attr.table <- nm$add.edges.extremities(e.subnet.attr.table, g, F)

		# Convert edge extremities to v.identity
		e.subnet.attr.table <- nm$convert.extremities.to.v.identity(e.subnet.attr.table,
			v.subnet.attr.table, 'sogi_identity', g)

		# Get attributes for edge identity
		e.identity.list <- c('source', 'target')
		for (attr in names(l$e.identity.list)) {
			if (as.logical(l$e.identity.list[attr])) {
				e.identity.list <- append(e.identity.list, attr)
			}
		}

		# Expand with missing attributes
		e.subnet.attr.table <- nm$expand.attr.table(e.subnet.attr.table,
			c(e.identity.list, names(l$e_behavior)))

		# Add edge identity column
		e.subnet.attr.table <- nm$add.collapsed.col(e.subnet.attr.table,
			e.identity.list, 'sogi_identity', '~')

		# Sort edge attribute table
		e.subnet.attr.table <- nm$sort.table.cols(e.subnet.attr.table)

		# CONTAINS #
		v.subnet.identity <- nm$get.col(v.subnet.attr.table, 'sogi_identity')
		v.supernet.identity <- nm$get.col(v.supernet.attr.table, 'sogi_identity')
		e.subnet.identity <- nm$get.col(e.subnet.attr.table, 'sogi_identity')
		e.supernet.identity <- nm$get.col(e.supernet.attr.table, 'sogi_identity')

		v.contains <- length(which(!v.subnet.identity %in% v.supernet.identity))
		e.contains <- length(which(!e.subnet.identity %in% e.supernet.identity))

		if ( 0 != v.contains) {
			cat('FALSE')
			stop()
		}

		if ( 0 != e.contains) {
			cat('FALSE')
			stop()
		}

		cat('TRUE')
	}
}