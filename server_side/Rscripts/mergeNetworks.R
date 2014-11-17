#!/usr/bin/env Rscript

options(echo=TRUE)
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

		cat('> Read config file\n')
		s <- scan(paste0(args[2], '.json'), 'raw')
		l <- fromJSON(s)

		n_attr_tables <- c()
		e_attr_tables <- c()

		cat('> Start process\n')
		# For each selected network
		for (network in l$networks) {
			# Read network
			g <- read.graph(paste0(network, '.graphml'), format='graphml')
			
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

				# Sort node attribute table
				n_attr_table <- n_attr_table[,order(colnames(n_attr_table))]

				# Rbind
				n_attr_tables <- rbind(n_attr_tables, n_attr_table)

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
							e_attr_table[,'source'] <- n_attr_table[unlist(lapply(get.edgelist(g)[,1], FUN=function(x,g) { return(which(V(g)$name == x)) }, g=g)), 'sogi_node_identity']
						} else {
							e_attr_table <- cbind(e_attr_table, n_attr_table[unlist(lapply(get.edgelist(g)[,1], FUN=function(x,g) { return(which(V(g)$name == x)) }, g=g)), 'sogi_node_identity'])
							colnames(e_attr_table)[ncol(e_attr_table)] <- 'source'
						}
						if ( 'target' %in% colnames(e_attr_table) ) {
							e_attr_table[,'target'] <- n_attr_table[unlist(lapply(get.edgelist(g)[,2], FUN=function(x,g) { return(which(V(g)$name == x)) }, g=g)), 'sogi_node_identity']
						} else {
							e_attr_table <- cbind(e_attr_table, n_attr_table[unlist(lapply(get.edgelist(g)[,2], FUN=function(x,g) { return(which(V(g)$name == x)) }, g=g)), 'sogi_node_identity'])
							colnames(e_attr_table)[ncol(e_attr_table)] <- 'target'
						}
					} else {
						if ( 'source' %in% colnames(e_attr_table) ) {
							e_attr_table[,'source'] <- n_attr_table[unlist(lapply(get.edgelist(g)[,1], FUN=function(x,g) { return(which(V(g)$id == x)) }, g=g)), 'sogi_node_identity']
						} else {
							e_attr_table <- cbind(e_attr_table, n_attr_table[unlist(lapply(get.edgelist(g)[,1], FUN=function(x,g) { return(which(V(g)$id == x)) }, g=g)), 'sogi_node_identity'])
							colnames(e_attr_table)[ncol(e_attr_table)] <- 'source'
						}
						if ( 'target' %in% colnames(e_attr_table) ) {
							e_attr_table <- n_attr_table[unlist(lapply(get.edgelist(g)[,2], FUN=function(x,g) { return(which(V(g)$id == x)) }, g=g)), 'sogi_node_identity']
						} else {
							e_attr_table[,'target'] <- cbind(e_attr_table, n_attr_table[unlist(lapply(get.edgelist(g)[,2], FUN=function(x,g) { return(which(V(g)$id == x)) }, g=g)), 'sogi_node_identity'])
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

					# Sort edge attribute table
					e_attr_table <- e_attr_table[,order(colnames(e_attr_table))]

					# Rbind
					e_attr_tables <- rbind(e_attr_tables, e_attr_table)
				}
			}
		}

		n_id_col <- n_attr_tables[,'sogi_node_identity']
		n_id_u_col <- unique(n_id_col)
		e_id_col <- e_attr_tables[,'sogi_edge_identity']
		e_id_u_col <- unique(e_id_col)

		cat('> Prepare node output table\n')
		# Prepare output node table
		n_out_table <- n_attr_tables[which(!duplicated(n_id_col)),]
		# Select behaviors
		n_b_list <- c()
		for (attr in names(l$n_behavior)) {
			if ( 'ignore' == l$n_behavior[attr] ) {
				if ( !is.null(nrow(n_out_table)) ) {
					n_out_table <- n_out_table[,-which(attr == colnames(n_out_table))]
					n_attr_tables <- n_attr_tables[,-which(attr == colnames(n_attr_tables))]
				} else {
					n_out_table <- n_out_table[-which(attr == names(n_out_table))]
					n_attr_tables <- n_attr_tables[-which(attr == names(n_attr_tables))]
				}
			} else {
				n_b_list <- append(n_b_list, attr)
			}
		}
		if ( !is.null(nrow(n_attr_tables)) ) {
			# Add column for new attribute
			if ( l$n_count_attr ) {
				n_out_table <- cbind(n_out_table, NA)
				colnames(n_out_table)[ncol(n_out_table)] <- 'merge_count'
			}
			# Act as indicated from behavior
			for (i in 1:length(n_id_u_col)) {
				sub_table <- n_attr_tables[which(n_id_col == n_id_u_col[i]),]
				for (attr in n_b_list) {
					if ( is.null(nrow(sub_table)) ) {
						n_out_table[i, attr] <- sub_table[attr]
					} else {
						if ( 'sum' == l$n_behavior[attr] ) {
							n_out_table[i, attr] <- sum(as.numeric(sub_table[, attr]))
						} else if ( 'prod' == l$n_behavior[attr] ) {
							n_out_table[i, attr] <- prod(as.numeric(sub_table[, attr]))
						} else if ( 'min' == l$n_behavior[attr] ) {
							n_out_table[i, attr] <- min(as.numeric(sub_table[, attr]))
						} else if ( 'max' == l$n_behavior[attr] ) {
							n_out_table[i, attr] <- max(as.numeric(sub_table[, attr]))
						} else if ( 'random' == l$n_behavior[attr] ) {
							n_out_table[i, attr] <- sub_table[round(runif(1,0,1)*(nrow(sub_table)-1))+1, attr]
						} else if ( 'mean' == l$n_behavior[attr] ) {
							n_out_table[i, attr] <- mean(as.numeric(sub_table[, attr]))
						} else if ( 'median' == l$n_behavior[attr] ) {
							n_out_table[i, attr] <- median(as.numeric(sub_table[, attr]))
						} else if ( 'concat' == l$n_behavior[attr] ) {
							n_out_table[i, attr] <- paste(sub_talbe[, attr], collapse='~')
						} else if ( 'first' == l$n_behavior[attr] ) {
							n_out_table[i, attr] <- sub_table[1, attr]
						} else if ( 'last' == l$n_behavior[attr] ) {
							n_out_table[i, attr] <- sub_table[nrow(sub_table), attr]
						}
					}
				}
				if ( l$n_count_attr ) {
					if ( is.null(nrow(sub_table)) ) {
						n_out_table[i, 'merge_count'] <- 1
					} else {
						n_out_table[i, 'merge_count'] <- nrow(sub_table)
					}
				}
			}
		}

		cat('> Prepare edge output table\n')
		# Prepare output edge table
		e_out_table <- e_attr_tables[which(!duplicated(e_attr_tables[,'sogi_edge_identity'])),]
		if ( !is.null(e_out_table) ) {
			# Select behaviors
			e_b_list <- c()
			for (attr in names(l$e_behavior)) {
				if ( !attr %in% c('source', 'target') ) {
					if ( 'ignore' == l$e_behavior[attr] ) {
						e_out_table <- e_out_table[,-which(attr == colnames(e_out_table))]
						e_attr_tables <- e_attr_tables[,-which(attr == colnames(e_attr_tables))]
					} else {
						e_b_list <- append(e_b_list, attr)
					}
				}
			}
			# Add column for new attribute
			if ( l$e_count_attr ) {
				e_out_table <- cbind(e_out_table, NA)
				colnames(e_out_table)[ncol(e_out_table)] <- 'merge_count'
			}
			# Act as indicated from behavior
			for (i in 1:length(e_id_u_col)) {
				sub_table <- e_attr_tables[which(e_id_col == e_id_u_col[i]),]
				for (attr in e_b_list) {
					if ( is.null(nrow(sub_table)) ) {
						e_out_table[i, attr] <- sub_table[attr]
					} else {
						if ( 'sum' == l$e_behavior[attr] ) {
							e_out_table[i, attr] <- sum(as.numeric(sub_table[, attr]))
						} else if ( 'prod' == l$e_behavior[attr] ) {
							e_out_table[i, attr] <- prod(as.numeric(sub_table[, attr]))
						} else if ( 'min' == l$e_behavior[attr] ) {
							e_out_table[i, attr] <- min(as.numeric(sub_table[, attr]))
						} else if ( 'max' == l$e_behavior[attr] ) {
							e_out_table[i, attr] <- max(as.numeric(sub_table[, attr]))
						} else if ( 'random' == l$e_behavior[attr] ) {
							e_out_table[i, attr] <- sub_table[round(runif(1,0,1)*(nrow(sub_table)-1))+1, attr]
						} else if ( 'mean' == l$e_behavior[attr] ) {
							e_out_table[i, attr] <- mean(as.numeric(sub_table[, attr]))
						} else if ( 'median' == l$e_behavior[attr] ) {
							e_out_table[i, attr] <- median(as.numeric(sub_table[, attr]))
						} else if ( 'concat' == l$e_behavior[attr] ) {
							e_out_table[i, attr] <- paste(sub_talbe[, attr], collapse='~')
						} else if ( 'first' == l$e_behavior[attr] ) {
							e_out_table[i, attr] <- sub_table[1, attr]
						} else if ( 'last' == l$e_behavior[attr] ) {
							e_out_table[i, attr] <- sub_table[nrow(sub_table), attr]
						}
					}
				}
				if ( l$e_count_attr ) {
					if ( is.null(nrow(sub_table)) ) {
						e_out_table[i, 'merge_count'] <- 1
					} else {
						e_out_table[i, 'merge_count'] <- nrow(sub_table)
					}
				}
			}
		}

		# Convert source/target in node IDs
		e_out_table[,'source'] <- unlist(lapply(e_out_table[,'source'], FUN=function (x, nlist) {
			return(which(nlist == x))
		}, nlist=n_out_table[,which('sogi_node_identity' == colnames(n_out_table))]))
		e_out_table[,'target'] <- unlist(lapply(e_out_table[,'target'], FUN=function (x, nlist) {
			return(which(nlist == x))
		}, nlist=n_out_table[,which('sogi_node_identity' == colnames(n_out_table))]))

		# Remove identity columns
		if ( !is.null(n_out_table) ) {
			if ( !is.null(nrow(n_out_table)) ) {
				n_out_table <- n_out_table[, -which('sogi_node_identity' == colnames(n_out_table))]
			} else {
				n_out_table <- n_out_table[-which('sogi_node_identity' == names(n_out_table))]
			}
		}
		if ( !is.null(e_out_table) ) {
			if ( !is.null(nrow(e_out_table)) ) {
				e_out_table <- e_out_table[, -which('sogi_edge_identity' == colnames(e_out_table))]
			} else {
				e_out_table <- e_out_table[-which('sogi_edge_identity' == names(e_out_table))]
			}
		}

		cat('> Convert to graph\n')
		g.out <- graph.empty()
		if ( !is.null(nrow(n_out_table)) ) {
			g.out <- add.vertices(g.out, nrow(n_out_table))
			for (attr in colnames(n_out_table)) {
				eval(parse(text=paste0('V(g.out)$', attr, ' <- n_out_table[, attr]')))
			}
		} else {
			g.out <- add.vertices(g.out, 1)
			for (attr in names(n_out_table)) {
				eval(parse(text=paste0('V(g.out)$', attr, ' <- n_out_table[attr]')))
			}
		}
		if ( !is.null(nrow(e_out_table)) ) {
			g.out <- add.edges(g.out, c(rbind(V(g.out)[as.numeric(e_out_table[,'source'])],V(g.out)[as.numeric(e_out_table[,'target'])])))
			for (attr in colnames(e_out_table)) {
				eval(parse(text=paste0('E(g.out)$', attr, ' <- e_out_table[, attr]')))
			}
		} else {
			g.out <- add.edges(g.out, c(rbind(V(g.out)[as.numeric(e_out_table['source'])],V(g.out)[as.numeric(e_out_table['target'])])))
			for (attr in colnames(e_out_table)) {
				eval(parse(text=paste0('E(g.out)$', attr, ' <- e_out_table[attr]')))
			}
		}

		cat('> Write GraphML network\n')
		write.graph(g.out, paste0(l$new_name, '.graphml'), format='graphml')

		cat('> Preparing config file.\n')
		d <- list(e_attributes=list.edge.attributes(g.out), e_count=ecount(g.out), v_attributes=list.vertex.attributes(g.out), v_count=vcount(g.out))

		cat('> Writing DAT file.\n')		
		write(toJSON(d), paste0(l$new_name, '.dat'))

		cat('> Writing JSON file.\n')
		source('../../Rscripts/extendIgraph.R')
		write.graph(g.out, paste0(l$new_name, '.json'), format='json')

		cat('> Converted.\n')

		cat('~ END ~')
	}
}