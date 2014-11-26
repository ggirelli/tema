#!/usr/bin/env Rscript

options(echo=TRUE)
args <- commandArgs(trailingOnly = TRUE)

# Check parameters
if(length(args) != 4) stop('./addAttributeToNetwork.R session_id graph_name attr_type attr_name')

# Load requirements
library(igraph)
library(rjson)

source('./Graph_Manager.class.R')
nm <- GraphManager()

# Start
if(file.exists(paste0('../session/', args[1], '/'))) {
	setwd(paste0('../session/', args[1], '/'))

	cat('> Read JSON file\n')
	s <- read.delim(paste0(args[2], '.json'), header = F, as.is=T, quote = "")[1,1]
	l <- fromJSON(s)

	attr.tables <- nm$graph.list.to.attr.tables(l)
	v.attr.table <- attr.tables$nodes
	e.attr.table <- attr.tables$edges

	cat('> Rename attribute\n')
	if ( 'nodes' == args[3] ) {
		v.attr.table <- nm$rm.cols(v.attr.table, c(args[4]))
	} else if ( 'edges' == args[3] ) {
		e.attr.table <- nm$rm.cols(e.attr.table, c(args[4]))
	}

	cat('> Convert back to JSON\n')
	graph.list <- nm$attr.tables.to.list(v.attr.table, e.attr.table)

	write(toJSON(graph.list), paste0(args[2], '.json'))
}