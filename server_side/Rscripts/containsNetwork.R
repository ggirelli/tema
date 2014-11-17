#!/usr/bin/env Rscript

#options(echo=TRUE)
args <- commandArgs(trailingOnly = TRUE)

# Check parameters
if(length(args) != 2) stop('./convertToJSON.R session_id config_file')

# Load requirements
library(igraph)
library(rjson)

# Start
if(file.exists(paste0('/home/gire/public_html/SOGIv020/server_side/session/', args[1], '/'))) {
	setwd(paste0('/home/gire/public_html/SOGIv020/server_side/session/', args[1], '/'))
	
	if(file.exists(paste0(args[2], '.json'))) {

		s <- scan(paste0(args[2], '.json'), 'raw')
		l <- fromJSON(s)

		# Read network
		g <- read.graph(paste0(l$sub, '.graphml'), format='graphml')
		# If there are nodes
		if ( 0 != vcount(g) ) {
			# Build node attribute table
			n_attr_table <- c()
			for (attr in list.vertex.attributes(g)) {
				n_attr_table <- cbind(n_attr_table, eval(parse(text=paste0('V(g)$', attr))))
			}
			colnames(n_attr_table) <- list.vertex.attributes(g)

			# Get attributes for node identity
			n_identity <- c()
			for (attr in names(l$n_identity)) {
				if (as.logical(l$n_identity[attr])) {
					n_identity <- append(n_identity, attr)
				}
			}

			# Expand with missing attributes
			for (attr in c(n_identity, names(l$n_behavior)) ) {
				if ( !attr %in% colnames(n_attr_table) ) {
					n_attr_table <- cbind(n_attr_table, NA)
					colnames(n_attr_table)[ncol(n_attr_table)] <- attr
				}
			}
			
			# Add node identity column
			n_identity_col <- c()
			for (attr in n_identity) {
				if ( 0 == length(n_identity_col) ) {
					n_identity_col <- n_attr_table[,attr]
				} else {
					n_identity_col <- paste0(n_identity_col, '~', n_attr_table[,attr])
				}
			}
			n_attr_table <- cbind(n_attr_table, n_identity_col)
			colnames(n_attr_table)[ncol(n_attr_table)] <- c('sogi_node_identity')

			# Append
			n_sub_identity <- n_attr_table[,which('sogi_node_identity' == colnames(n_attr_table))]

			# If there are edges
			if ( 0 != ecount(g) ) {
				# Build edge attribute table
				e_attr_table <- c()
				for (attr in list.edge.attributes(g)) {
					e_attr_table <- cbind(e_attr_table, eval(parse(text=paste0('E(g)$', attr))))
				}
				colnames(e_attr_table) <- list.edge.attributes(g)

				# Add source/target
				if ( 'name' %in% list.vertex.attributes(g) ) {
					if ( 'source' %in% colnames(e_attr_table) ) {
						e_attr_table[,'source'] <- n_attr_table[unlist(lapply(get.edgelist(g)[,1], FUN=function(x,g) {return( which(V(g)$name == x)) }, g=g)), 'sogi_node_identity']
					} else {
						e_attr_table <- cbind(e_attr_table, n_attr_table[unlist(lapply(get.edgelist(g)[,1], FUN=function(x,g) {return( which(V(g)$name == x)) }, g=g)), 'sogi_node_identity'])
						colnames(e_attr_table)[ncol(e_attr_table)] <- 'source'
					}
					if ( 'target' %in% colnames(e_attr_table) ) {
						e_attr_table[,'target'] <- n_attr_table[unlist(lapply(get.edgelist(g)[,2], FUN=function(x,g) {return( which(V(g)$name == x)) }, g=g)), 'sogi_node_identity']
					} else {
						e_attr_table <- cbind(e_attr_table, n_attr_table[unlist(lapply(get.edgelist(g)[,2], FUN=function(x,g) {return( which(V(g)$name == x)) }, g=g)), 'sogi_node_identity'])
						colnames(e_attr_table)[ncol(e_attr_table)] <- 'target'
					}
				} else {
					if ( 'source' %in% colnames(e_attr_table) ) {
						e_attr_table[,'source'] <- n_attr_table[unlist(lapply(get.edgelist(g)[,1], FUN=function(x,g) {return( which(V(g)$id == x)) }, g=g)), 'sogi_node_identity']
					} else {
						e_attr_table <- cbind(e_attr_table, n_attr_table[unlist(lapply(get.edgelist(g)[,1], FUN=function(x,g) {return( which(V(g)$id == x)) }, g=g)), 'sogi_node_identity'])
						colnames(e_attr_table)[ncol(e_attr_table)] <- 'source'
					}
					if ( 'target' %in% colnames(e_attr_table) ) {
						e_attr_table <- n_attr_table[unlist(lapply(get.edgelist(g)[,2], FUN=function(x,g) {return( which(V(g)$id == x)) }, g)), 'sogi_node_identity']
					} else {
						e_attr_table[,'target'] <- cbind(e_attr_table, n_attr_table[unlist(lapply(get.edgelist(g)[,2], FUN=function(x,g) {return( which(V(g)$id == x)) }, g=g)), 'sogi_node_identity'])
						colnames(e_attr_table)[ncol(e_attr_table)] <- 'target'
					}
				}

				# Get attributes for edge identity
				e_identity <- c()
				for (attr in names(l$e_identity)) {
					if (as.logical(l$e_identity[attr])) {
						e_identity <- append(e_identity, attr)
					}
				}

				# Expand with missing attributes
				for (attr in c(e_identity, names(l$e_behavior)) ) {
					if ( !attr %in% colnames(e_attr_table) ) {
						e_attr_table <- cbind(e_attr_table, NA)
						colnames(e_attr_table)[ncol(e_attr_table)] <- attr
					}
				}

				# Add edge identity column
				e_identity_col <- paste0(e_attr_table[,'source'], '->', e_attr_table[,'target'])
				for (attr in e_identity) {
					if ( 0 == length(e_identity_col) ) {
						e_identity_col <- e_attr_table[,attr]
					} else {
						e_identity_col <- paste0(e_identity_col, '~', e_attr_table[,attr])
					}
				}
				e_attr_table <- cbind(e_attr_table, e_identity_col)
				colnames(e_attr_table)[ncol(e_attr_table)] <- c('sogi_edge_identity')

				# Append
				e_sub_identity <- e_attr_table[,which('sogi_edge_identity' == colnames(e_attr_table))]
			} else {
				e_sub_identity <- c()
			}
		} else {
			n_sub_identity <- c()
			e_sub_identity <- c()
		}

		# Work on the super network
		# Read network
		g <- read.graph(paste0(l$super, '.graphml'), format='graphml')
		# If there are nodes
		if ( 0 != vcount(g) ) {
			# Build node attribute table
			n_super_attr_table <- c()
			for (attr in list.vertex.attributes(g)) {
				n_super_attr_table <- cbind(n_super_attr_table, eval(parse(text=paste0('V(g)$', attr))))
			}
			colnames(n_super_attr_table) <- list.vertex.attributes(g)

			# Get attributes for node identity
			n_identity <- c()
			for (attr in names(l$n_identity)) {
				if (as.logical(l$n_identity[attr])) {
					n_identity <- append(n_identity, attr)
				}
			}

			# Expand with missing attributes
			for (attr in c(n_identity, names(l$n_behavior)) ) {
				if ( !attr %in% colnames(n_super_attr_table) ) {
					n_super_attr_table <- cbind(n_super_attr_table, NA)
					colnames(n_super_attr_table)[ncol(n_super_attr_table)] <- attr
				}
			}
			
			# Add node identity column
			n_identity_col <- c()
			for (attr in n_identity) {
				if ( 0 == length(n_identity_col) ) {
					n_identity_col <- n_super_attr_table[,attr]
				} else {
					n_identity_col <- paste0(n_identity_col, '~', n_super_attr_table[,attr])
				}
			}
			n_super_attr_table <- cbind(n_super_attr_table, n_identity_col)
			colnames(n_super_attr_table)[ncol(n_super_attr_table)] <- c('sogi_node_identity')

			# Sort node attribute table
			n_super_attr_table <- n_super_attr_table[,order(colnames(n_super_attr_table))]

			# If there are edges
			if ( 0 != ecount(g) ) {
				# Build edge attribute table
				e_super_attr_table <- c()
				for (attr in list.edge.attributes(g)) {
					e_super_attr_table <- cbind(e_super_attr_table, eval(parse(text=paste0('E(g)$', attr))))
				}
				colnames(e_super_attr_table) <- list.edge.attributes(g)

				# Add source/target
				if ( 'name' %in% list.vertex.attributes(g) ) {
					if ( 'source' %in% colnames(e_super_attr_table) ) {
						e_super_attr_table[,'source'] <- n_super_attr_table[unlist(lapply(get.edgelist(g)[,1], FUN=function(x,g) {return( which(V(g)$name == x)) }, g=g)), 'sogi_node_identity']
					} else {
						e_super_attr_table <- cbind(e_super_attr_table, n_super_attr_table[unlist(lapply(get.edgelist(g)[,1], FUN=function(x,g) {return( which(V(g)$name == x)) }, g=g)), 'sogi_node_identity'])
						colnames(e_super_attr_table)[ncol(e_super_attr_table)] <- 'source'
					}
					if ( 'target' %in% colnames(e_super_attr_table) ) {
						e_super_attr_table[,'target'] <- n_super_attr_table[unlist(lapply(get.edgelist(g)[,2], FUN=function(x,g) {return( which(V(g)$name == x)) }, g=g)), 'sogi_node_identity']
					} else {
						e_super_attr_table <- cbind(e_super_attr_table, n_super_attr_table[unlist(lapply(get.edgelist(g)[,2], FUN=function(x,g) {return( which(V(g)$name == x)) }, g=g)), 'sogi_node_identity'])
						colnames(e_super_attr_table)[ncol(e_super_attr_table)] <- 'target'
					}
				} else {
					if ( 'source' %in% colnames(e_super_attr_table) ) {
						e_super_attr_table[,'source'] <- n_super_attr_table[unlist(lapply(get.edgelist(g)[,1], FUN=function(x,g) {return( which(V(g)$id == x)) }, g=g)), 'sogi_node_identity']
					} else {
						e_super_attr_table <- cbind(e_super_attr_table, n_super_attr_table[unlist(lapply(get.edgelist(g)[,1], FUN=function(x,g) {return( which(V(g)$id == x)) }, g=g)), 'sogi_node_identity'])
						colnames(e_super_attr_table)[ncol(e_super_attr_table)] <- 'source'
					}
					if ( 'target' %in% colnames(e_super_attr_table) ) {
						e_super_attr_table <- n_super_attr_table[unlist(lapply(get.edgelist(g)[,2], FUN=function(x,g) {return( which(V(g)$id == x)) }, g)), 'sogi_node_identity']
					} else {
						e_super_attr_table[,'target'] <- cbind(e_super_attr_table, n_super_attr_table[unlist(lapply(get.edgelist(g)[,2], FUN=function(x,g) {return( which(V(g)$id == x)) }, g=g)), 'sogi_node_identity'])
						colnames(e_super_attr_table)[ncol(e_super_attr_table)] <- 'target'
					}
				}

				# Get attributes for edge identity
				e_identity <- c()
				for (attr in names(l$e_identity)) {
					if (as.logical(l$e_identity[attr])) {
						e_identity <- append(e_identity, attr)
					}
				}

				# Expand with missing attributes
				for (attr in c(e_identity, names(l$e_behavior)) ) {
					if ( !attr %in% colnames(e_super_attr_table) ) {
						e_super_attr_table <- cbind(e_super_attr_table, NA)
						colnames(e_super_attr_table)[ncol(e_super_attr_table)] <- attr
					}
				}

				# Add edge identity column
				e_identity_col <- paste0(e_super_attr_table[,'source'], '->', e_super_attr_table[,'target'])
				for (attr in e_identity) {
					if ( 0 == length(e_identity_col) ) {
						e_identity_col <- e_super_attr_table[,attr]
					} else {
						e_identity_col <- paste0(e_identity_col, '~', e_super_attr_table[,attr])
					}
				}
				e_super_attr_table <- cbind(e_super_attr_table, e_identity_col)
				colnames(e_super_attr_table)[ncol(e_super_attr_table)] <- c('sogi_edge_identity')

				# Sort edge attribute table
				e_super_attr_table <- e_super_attr_table[,order(colnames(e_super_attr_table))]
			}
		}

		# Contains
		n_contains <- length(which(!n_sub_identity %in% n_super_attr_table[,which('sogi_node_identity' == colnames(n_super_attr_table))]))
		e_contains <- length(which(!e_sub_identity %in% e_super_attr_table[,which('sogi_edge_identity' == colnames(e_super_attr_table))]))
		
		if ( 0 != n_contains ) {
			cat('FALSE')
			stop()
		}
		if ( 0 != e_contains ) {
			cat('FALSE')
			stop()
		}
		cat('TRUE')

	}
}