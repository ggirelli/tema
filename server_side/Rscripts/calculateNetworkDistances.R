#!/usr/bin/env Rscript

#options(echo=FALSE, warn=-1)
args <- commandArgs(trailingOnly = TRUE)

# Check parameters
if(length(args) != 2) stop('./convertToJSON.R session_id config_file')

# Load requirements
library(igraph)
library(rjson)
library(heatmap.plus)

source('NetworkManager.class.R')
nm <- NetworkManager()

source('GraphManager.class.R')
gm <- GraphManager()

plot.heatmap = function(ds.matrix, d.col, file.path, main) {
	# Plots the heatmap
	# 
	# Args:
	# 	ds.matrix: the distances matrix
	# 	d.col: the label of the distance column
	# 	file.path: for output in svg format
	# 	main: the heatmap main title

	d.matrix <- matrix(NA, ncol=length(net.list), nrow=length(net.list))
	colnames(d.matrix) <- net.list
	rownames(d.matrix) <- net.list
	for (k in 1:nrow(ds.matrix)) {
		i <- which(net.list == ds.matrix[k,1])
		j <- which(net.list == ds.matrix[k,2])
		d.matrix[i,j] <- as.numeric(ds.matrix[, which(colnames(ds.matrix) == d.col)][k])
		d.matrix[i,i] <- 0
		d.matrix[j,i] <- as.numeric(ds.matrix[, which(colnames(ds.matrix) == d.col)][k])
		d.matrix[j,j] <- 0
	}

	svg(file.path)
	rownames(d.matrix) <- colnames(d.matrix)
	heatmap.plus::heatmap.plus(d.matrix, na.rm=F, symm=T, main=main, Rowv=NA, Colv=NA, margins=c(5,5))
	dev.off()
}

# Start
if(file.exists(paste0('../session/', args[1], '/'))) {
	setwd(paste0('../session/', args[1], '/'))
	
	if(file.exists(paste0(args[2], '.json'))) {

		s <- scan(paste0(args[2], '.json'), 'raw')
		l <- fromJSON(s)
		
		net.list <- c()
		v.attr.table.list <- list()
		e.attr.table.list <- list()

		# For each selected network
		for (network in l$networks) {

			# Read network
			g <- read.graph(paste0(network, '.graphml'), format='graphml')

			# Build attribute tables
			graph.list <- nm$graph.to.attr.table(g)
			v.attr.table <- graph.list$nodes
			e.attr.table <- graph.list$edges

			# VERTICES #
			
			# Get attributes for vertex identity
			v.identity.list <- c()
			for (attr in names(l$n_identity)) {
				if (as.logical(l$n_identity[attr])) {
					v.identity.list <- append(v.identity.list, attr)
				}
			}
			
			# Expand with missing attributes
			v.attr.table <- nm$expand.attr.table(v.attr.table, l$node_attr_list)
			
			# Add vertex identity column
			v.attr.table <- nm$add.collapsed.col(v.attr.table,
				v.identity.list, 'tea_identity', '~')
			
			# Sort v.attr.table columns
			v.attr.table <- nm$sort.table.cols(v.attr.table)
			
			# EDGES #

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
			e.attr.table <- nm$expand.attr.table(e.attr.table, l$edge_attr_list)

			# Add edge identity column
			e.attr.table <- nm$add.collapsed.col(e.attr.table,
				e.identity.list, 'tea_identity', '~')

			# Sort edge attribute table
			e.attr.table <- nm$sort.table.cols(e.attr.table)

			# MAKE LISTS #
			if ( 0 != nm$count.rows(e.attr.table) ) {
				v.attr.table.list <- nm$append.to.table.list(v.attr.table.list, v.attr.table)
				e.attr.table.list <- nm$append.to.table.list(e.attr.table.list, e.attr.table)
				net.list <- append(net.list, network)
			}
		}

		distances <- c()
		done.list <- c()
		for (i in 1:length(v.attr.table.list)) {
			for (j in 1:length(v.attr.table.list)) {
				if ( i != j && !paste0(j, '_', i) %in% done.list ) {

					# MERGE VERTICES #

					v.attr.table.one <- v.attr.table.list[[i]]
					v.attr.table.two <- v.attr.table.list[[j]]

					v.attr.table.merged <- nm$merge.tables.from.table.list(list(
						v.attr.table.one, v.attr.table.two))
					v.attr.table.merged <- nm$rm.duplicated.identity(
						v.attr.table.merged, 'tea_identity')

					# Update IDs
					v.attr.table <- nm$update.row.ids(v.attr.table.merged)
					v.attr.table <- nm$add.prefix.to.col(v.attr.table, 'id', 'n')

					# I-GRAPH EDGES #
					
					e.attr.table.one <- e.attr.table.list[[i]]

					# Convert extremities to IDs
					e.attr.table.one <- nm$convert.extremities.to.v.id.based.on.table(
						e.attr.table.one, v.attr.table, 'tea_identity')

					# J-GRAPH EDGES #

					e.attr.table.two <- e.attr.table.list[[j]]

					# Convert extremities to IDs
					e.attr.table.two <- nm$convert.extremities.to.v.id.based.on.table(
						e.attr.table.two, v.attr.table, 'tea_identity')

					# CALCULATE #
					
					single.row <- c(net.list[i], net.list[j])

					e.common <- length(intersect(
						nm$get.col(e.attr.table.one, 'tea_identity'),
						nm$get.col(e.attr.table.two, 'tea_identity')
					))

					# IPSEN MIKHAILOV #
					
					if ( l$dist$im || l$dist$him || l$dist$jim || l$dist$jsim ) {

						# Build i-graph
						g.one <- nm$attr.tables.to.graph(v.attr.table, e.attr.table.one)

						# Build j-graph
						g.two <- nm$attr.tables.to.graph(v.attr.table, e.attr.table.two)

						# Calc distance
						dIM <- gm$calcIpsenDist(g.one, g.two)
						if ( l$dist$im ) single.row <- append(single.row, dIM)

					}

					# HAMMING DISTANCE #
					
					if ( l$dist$h || l$dist$him ) {

						v.count <- nm$count.rows(v.attr.table)
						e.total.h <- v.count * (v.count - 1)

						# Calc distance
						dH <- 1 - (e.common / e.total.h)
						if ( l$dist$h ) single.row <- append(single.row, dH)

					}

					# HIM DISTANCE #
					
					if ( l$dist$him ) {

						xi <- 1
						dHIM <- (1/sqrt(1+xi)) * sqrt(dH**2 + xi * dIM**2)
						single.row <- append(single.row, dHIM)

					}

					# JACCARD DISTANCE #

					if ( l$dist$j || l$dist$jim ) {

						e.total <- length(unique(union(
							nm$get.col(e.attr.table.one, 'tea_identity'),
							nm$get.col(e.attr.table.two, 'tea_identity')
						)))

						# Calc distance
						dJ <- 1 - (e.common / e.total)
						if ( l$dist$j ) single.row <- append(single.row, dJ)

					}


					# JIM DISTANCE #
					
					if ( l$dist$jim ) {

						xi <- 1
						dJIM <- (1/sqrt(1+xi)) * sqrt(dJ**2 + xi * dIM**2)
						single.row <- append(single.row, dJIM)

					}

					# JACCARD SUBSET DISTANCE #
					
					if ( l$dist$js || l$dist$jsim ) {

						e.total.sub <- min(nm$count.rows(e.attr.table.one),
							nm$count.rows(e.attr.table.two))

						# Calc distance
						dJs <- 1 - (e.common / e.total.sub)
						if ( l$dist$js ) single.row <- append(single.row, dJs)

					}


					# JSIM DISTANCE #
					
					if ( l$dist$jsim ) {

						xi <- 1
						dJsIM <- (1/sqrt(1+xi)) * sqrt(dJs**2 + xi * dIM**2)
						single.row <- append(single.row, dJsIM)

					}

					# ASSEMBLE #

					distances <- rbind(distances, single.row)

					# END FOR network #
					done.list <- append(done.list, paste0(i, '_', j))
				}
			}
		}

		colnames <- c('g.one', 'g.two')
		if ( l$dist$im ) colnames <- append(colnames, 'dIM')
		if ( l$dist$h ) colnames <- append(colnames, 'dH')
		if ( l$dist$him ) colnames <- append(colnames, 'dHIM')
		if ( l$dist$j ) colnames <- append(colnames, 'dJ')
		if ( l$dist$jim ) colnames <- append(colnames, 'dJIM')
		if ( l$dist$js ) colnames <- append(colnames, 'dJs')
		if ( l$dist$jsim ) colnames <- append(colnames, 'dJsIM')
		distances <- nm$add.col.names(distances, colnames)

		write.table(distances, paste0('output_directory/dist_table.dat'), quote=F, row.names=F, sep='\t')

		if ( l$out_plot ) {
			row.names(distances) <-NULL
			if ( l$dist$im ) plot.heatmap(distances, 'dIM', paste0('output_directory/im_heatmap.svg'), 'Ipsen Mikhailov')
			if ( l$dist$h ) plot.heatmap(distances, 'dH', paste0('output_directory/h_heatmap.svg'), 'Hamming')
			if ( l$dist$him ) plot.heatmap(distances, 'dHIM', paste0('output_directory/him_heatmap.svg'), 'HIM')
			if ( l$dist$j ) plot.heatmap(distances, 'dJ', paste0('output_directory/j_heatmap.svg'), 'Jaccard')
			if ( l$dist$jim ) plot.heatmap(distances, 'dJIM', paste0('output_directory/jim_heatmap.svg'), 'JIM')
			if ( l$dist$js ) plot.heatmap(distances, 'dJs', paste0('output_directory/js_heatmap.svg'), 'Jaccard subset')
			if ( l$dist$jsim ) plot.heatmap(distances, 'dJsIM', paste0('output_directory/jsim_heatmap.svg'), 'JsIM')
		}
	}
}