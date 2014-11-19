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

					# JACCARD DISTANCE #
					
					e.common <- length(intersect(
						nm$get.col(e.attr.table.one, 'tea_identity'),
						nm$get.col(e.attr.table.two, 'tea_identity')
					))

					if ( l$dist$j ) {

						e.total <- length(unique(union(
							nm$get.col(e.attr.table.one, 'tea_identity'),
							nm$get.col(e.attr.table.two, 'tea_identity')
						)))

						# Calc distance
						dJ <- 1 - (e.common / e.total)
						single.row <- append(single.row, dJ)

					}

					# JACCARD SUBSET DISTANCE #
					
					if ( l$dist$js ) {

						e.total.sub <- min(nm$count.rows(e.attr.table.one),
							nm$count.rows(e.attr.table.two))

						# Calc distance
						dJs <- 1 - (e.common / e.total.sub)
						single.row <- append(single.row, dJs)

					}

					# IPSEN MIKHAILOV #
					
					if ( l$dist$im ) {

						# Build i-graph
						g.one <- nm$attr.tables.to.graph(v.attr.table, e.attr.table.one)

						# Build j-graph
						g.two <- nm$attr.tables.to.graph(v.attr.table, e.attr.table.two)

						# Calc distance
						dIM <- gm$calcIpsenDist(g.one, g.two)
						single.row <- append(single.row, dIM)

					}
					distances <- rbind(distances, single.row)

					# END FOR network #
					done.list <- append(done.list, paste0(i, '_', j))
				}
			}
		}

		colnames <- c('g.one', 'g.two')
		if ( l$dist$j ) colnames <- append(colnames, 'dJ')
		if ( l$dist$js ) colnames <- append(colnames, 'dJs')
		if ( l$dist$im ) colnames <- append(colnames, 'dIM')
		distances <- nm$add.col.names(distances, colnames)
		
		time_token <- as.numeric(Sys.time())
		if ( l$out_table ) {
			write.table(distances, paste0('output_directory/', round(time_token), '_dist_table.dat'),
				quote=F, row.names=F, sep='\t')
		}
		
		if ( l$out_plot ) {
			row.names(distances) <-NULL
			distances <- as.data.frame(distances, stringsAsFactors=F)
			if ( l$dist$j ) {
				dJ.matrix <- matrix(NA, ncol=length(net.list), nrow=length(net.list))
				colnames(dJ.matrix) <- net.list
				rownames(dJ.matrix) <- net.list
				for (k in 1:nrow(distances)) {
					i <- which(net.list == distances[k,1])
					j <- which(net.list == distances[k,2])
					dJ.matrix[i,j] <- as.numeric(distances$dJ[k])
					dJ.matrix[i,i] <- 0
					dJ.matrix[j,i] <- as.numeric(distances$dJ[k])
					dJ.matrix[j,j] <- 0
				}
				
				svg(paste0('output_directory/', round(time_token), '_j_heatmap.svg'))
				rownames(dJ.matrix) <- colnames(dJ.matrix)
				heatmap.plus::heatmap.plus(dJ.matrix, na.rm=F, symm=T, main='Jaccard', Rowv=NA, Colv=NA, margins=c(5,5))
				dev.off()
			}
			if ( l$dist$js ) {
				dJs.matrix <- matrix(NA, ncol=length(net.list), nrow=length(net.list))
				colnames(dJs.matrix) <- net.list
				rownames(dJs.matrix) <- net.list
				for (k in 1:nrow(distances)) {
					i <- which(net.list == distances[k,1])
					j <- which(net.list == distances[k,2])
					dJs.matrix[i,j] <- as.numeric(distances$dJs[k])
					dJs.matrix[i,i] <- 0
					dJs.matrix[j,i] <- as.numeric(distances$dJs[k])
					dJs.matrix[j,j] <- 0
				}
				
				svg(paste0('output_directory/', round(time_token), '_js_heatmap.svg'))
				rownames(dJs.matrix) <- colnames(dJs.matrix)
				heatmap.plus::heatmap.plus(dJs.matrix, na.rm=F, symm=T, main='Jaccard subset', Rowv=NA, Colv=NA, margins=c(5,5))
				dev.off()
			}
			if ( l$dist$im ) {
				dIM.matrix <- matrix(NA, ncol=length(net.list), nrow=length(net.list))
				colnames(dIM.matrix) <- net.list
				rownames(dIM.matrix) <- net.list
				for (k in 1:nrow(distances)) {
					i <- which(net.list == distances[k,1])
					j <- which(net.list == distances[k,2])
					dIM.matrix[i,j] <- as.numeric(distances$dIM[k])
					dIM.matrix[i,i] <- 0
					dIM.matrix[j,i] <- as.numeric(distances$dIM[k])
					dIM.matrix[j,j] <- 0
				}

				svg(paste0('output_directory/', round(time_token), '_im_heatmap.svg'))
				rownames(dIM.matrix) <- colnames(dIM.matrix)
				heatmap.plus::heatmap.plus(dIM.matrix, na.rm=F, symm=T, main='Ipsen Mikhailov', Rowv=NA, Colv=NA, margins=c(5,5))
				dev.off()
			}
		}
		cat(time_token)
	}
}