#!/usr/bin/env Rscript

options(echo=TRUE)
args <- commandArgs(trailingOnly = TRUE)

# Check parameters
if(length(args) != 6) stop('./combinenNetworkAttributes.R session_id graph_name attr_type attr_name attr_list attr_function')

# Load requirements
library(igraph)
library(rjson)

source('./NetworkManager.class.R')
nm <- NetworkManager()

# Start
if (file.exists(paste0('../session/', args[1], '/'))) {
	setwd(paste0('../session/', args[1], '/'))

	cat('> Read JSON file\n')
	s <- read.delim(paste0(args[2], '.json'), header = F, as.is=T, quote = "")[1,1]
	l <- fromJSON(s)
	print(l)
	g <- nm$graph.list.to.graph(l)
	print(E(g))
	print(g)

	a_list <- list()
	cat('> Add attribute\n')
	if ( 'edges' == args[3] ) {
		fun <- args[6]
		attr_list <- unlist(strsplit(args[5], ',', fixed=T))
		for (i in 1:length(attr_list)) {
			if ( 0 == length(which(is.na(as.numeric(eval(parse(text=paste0('E(g)$', attr_list[i]))))))) ) {
				eval(parse(text=paste0('a_list[', i, '] <- as.numeric(as.character(E(g)$', attr_list[i], '))')))
			}
			fun <- gsub(paste0('_', i-1, '_'), paste0('a_list[[', i, ']]'), fun)
		}
		eval(parse(text=paste0('E(g)$', args[4], ' <- ', fun)))
	} else if ( 'nodes' == args[3] ) {
		fun <- args[6]
		attr_list <- unlist(strsplit(args[5], ',', fixed=T))
		for (i in 1:length(attr_list)) {
			if ( 0 == length(which(is.na(as.numeric(eval(parse(text=paste0('V(g)$', attr_list[i]))))))) ) {
				eval(parse(text=paste0('a_list[', i, '] <- as.numeric(as.character(V(g)$', attr_list[i], '))')))
			}
			fun <- gsub(paste0('_', i-1, '_'), paste0('a_list[[', i, ']]'), fun)
		}
		eval(parse(text=paste0('V(g)$', args[4], ' <- ', fun)))
	}
	
	graph.list <- nm$graph.to.attr.table(g)
	graph.list$nodes <- nm$update.row.ids(graph.list$nodes)
	graph.list$nodes <- nm$add.prefix.to.col(graph.list$nodes, 'id', 'n')
	graph.list$edges <- nm$convert.extremities.to.v.id.based.on.table(graph.list$edges,
		graph.list$nodes, 'name')
	graph.list$edges <- nm$update.row.ids(graph.list$edges)
	graph.list$edges <- nm$add.prefix.to.col(graph.list$edges, 'id', 'e')
	write(toJSON(nm$attr.tables.to.list(graph.list$nodes, graph.list$edges)),
		paste0(args[2], '.json'))
}
