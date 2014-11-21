#!/usr/bin/env Rscript

options(echo=TRUE)
args <- commandArgs(trailingOnly = TRUE)

# Check parameters
if(length(args) != 5) stop('./addGOattributueToNetwork.R session_id graph_name go_mgmt_type attr_name attr_hugo')

# Load requirements
library(igraph)
library(rjson)

source('NetworkManager.class.R')
nm <- NetworkManager()

# Start
if(file.exists(paste0('../session/', args[1], '/'))) {
	setwd(paste0('../session/', args[1], '/'))

	cat('> Read JSON file\n')
	s <- scan(paste0(args[2], '.json'), 'raw')
	l <- fromJSON(s)
	
	if ( 'default' == args[3] ) {
		
		# Default GO-mgmt
		rdata <- file.path('../..', 'static/go_mgmt.Rdata')
		
		if ( !file.exists(rdata) ) stop(1)
		load(rdata)
		
		attr.tables <- nm$graph.list.to.attr.tables(l)
		g <- nm$attr.tables.to.graph(attr.tables$nodes, attr.tables$edges)
		eval(parse(text=paste0('V(g)$', args[4], ' <- NA')))
		for (i in 1:vcount(g)) {
			eval(parse(text=paste0('V(g)[i]$', args[4], ' <- go.list[V(g)[i]$', args[5], ']')))
		}
		graph.list <- nm$graph.to.list(g)
		write(toJSON(graph.list), paste0(args[2], '.json'))

	} else if ( 'custom' == args[3] ) {

		# Custom GO-mgmt
		rdata <- file.path('.', 'settings/go_mgmt.Rdata')
		if ( !file.exists(rdata) ) stop()
		load(rdata)



	}
}