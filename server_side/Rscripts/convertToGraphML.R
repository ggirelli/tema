#!/usr/bin/env Rscript

options(echo=TRUE)
args <- commandArgs(trailingOnly = TRUE)

# Check parameters
if(length(args) != 2) stop('./convertToJSON.R session_id graph_name')

# Load requirements
library(igraph)
library(rjson)

# Start
if(file.exists(paste0('/home/gire/public_html/SOGIv020/server_side/session/', args[1], '/'))) {
	setwd(paste0('/home/gire/public_html/SOGIv020/server_side/session/', args[1], '/'))

	cat('> Read JSON file\n')
	s <- scan(paste0(args[2], '.json'), 'raw')
	l <- fromJSON(s)

	# NODES
	
	nodes <- unlist(l$nodes)
	nodes.attrs <- unique(names(nodes))
	nodes.attrs.clean <- unlist(lapply(nodes.attrs, FUN=function (x) { return( paste(unlist(strsplit(x, '.', fixed=T))[-1], collapse='.') ); }))
	nodes.table <- c()
	for (i in 1:length(nodes.attrs)) {
		attr <- nodes.attrs[i]
		if ( "" != nodes.attrs.clean[i] ) nodes.table <- cbind(nodes.table, nodes[which(names(nodes) == attr)])
	}
	colnames(nodes.table) <- nodes.attrs.clean[nodes.attrs.clean != ""]
	row.names(nodes.table) <- NULL
	nodes.table <- data.frame(nodes.table)

	edges <- unlist(l$edges)
	edges.attrs <- unique(names(edges))
	edges.attrs.clean <- unlist(lapply(edges.attrs, FUN=function (x) { return( paste(unlist(strsplit(x, '.', fixed=T))[-1], collapse='.') ); }))
	edges.table <- c()
	for (i in 1:length(edges.attrs)) {
		attr <- edges.attrs[i]
		if ( "" != edges.attrs.clean[i] ) edges.table <- cbind(edges.table, edges[which(names(edges) == attr)])
	}
	colnames(edges.table) <- edges.attrs.clean[edges.attrs.clean != ""]
	row.names(edges.table) <- NULL
	edges.table <- data.frame(edges.table)

	cat('> Convert to GraphML\n')
	options(warn=-1)

	g <- graph.empty()
	g <- g + vertices(1:nrow(nodes.table))
	for (attr in colnames(nodes.table)) {
		n <- eval(parse(text=paste0('as.numeric(as.character(nodes.table$', attr, '))')))
		if ( NA %in% n ) {
			eval(parse(text=paste0('V(g)$', attr, ' <- as.character(nodes.table$', attr, ')')))
		} else {
			eval(parse(text=paste0('V(g)$', attr, ' <- as.numeric(nodes.table$', attr, ')')))
		}
	}

	g <- g + edges(c(t(rbind(edges.table$source, edges.table$target))))
	for (attr in colnames(edges.table)) {
		if ( !attr %in% c('source', 'target') ) {
			n <- eval(parse(text=paste0('as.numeric(as.character(edges.table$', attr, '))')))
			if ( NA %in% n ) {
				eval(parse(text=paste0('E(g)$', attr, ' <- as.character(edges.table$', attr, ')')))
			} else {
				eval(parse(text=paste0('E(g)$', attr, ' <- as.numeric(edges.table$', attr, ')')))
			}
		}
	}
	print(g)
	print(V(g))
	print(V(g)$id)
	print(V(g)$name)
	print(V(g)$x)
	print(V(g)$y)
	print(E(g)$id)
	cat('> Write GraphML file\n')
	write.graph(g, paste0(args[2], '.graphml'), format='graphml')

	cat('> Write DAT file\n')
	l <- list(e_attributes=list.edge.attributes(g), e_count=ecount(g), v_attributes=list.vertex.attributes(g), v_count=vcount(g))
	write(toJSON(l), paste0(args[2], '.dat'))

}