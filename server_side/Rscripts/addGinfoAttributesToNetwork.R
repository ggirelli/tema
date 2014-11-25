#!/usr/bin/env Rscript

options(echo=TRUE)
args <- commandArgs(trailingOnly = TRUE)

# Check parameters
if(length(args) != 4) stop('./addGinfoAttributesToNetwork.R session_id graph_name go_mgmt_type attr_hugo')

# Load requirements
library(igraph)
library(rjson)

library(biomaRt)
ensembl <- useMart(
	biomart = "ENSEMBL_MART_ENSEMBL",
    host = "grch37.ensembl.org",
    path="/biomart/martservice",
    archive=FALSE,
    dataset = "hsapiens_gene_ensembl"
)
normal.chroms <- c(1:22, "X", "Y")

source('NetworkManager.class.R')
nm <- NetworkManager()

# Start
if(file.exists(paste0('../session/', args[1], '/'))) {
	setwd(paste0('../session/', args[1], '/'))

	cat('> Read JSON file\n')
	s <- read.delim(paste0(args[2], '.json'), header = F, as.is=T, quote = "")[1,1]
	l <- fromJSON(s)
	
	if ( 'default' == args[3] ) {
		
		# Default GO-mgmt
		rdata <- file.path('../..', 'static/go_mgmt.Rdata')
		
		if ( !file.exists(rdata) ) stop(1)
		load(rdata)
		
		attr.tables <- nm$graph.list.to.attr.tables(l)
		g <- nm$attr.tables.to.graph(attr.tables$nodes, attr.tables$edges)
		eval(parse(text=paste0('V(g)$', 'go', ' <- NA')))
		for (i in 1:vcount(g)) {
			eval(parse(text=paste0('V(g)[i]$', 'go', ' <- go.list[V(g)[i]$', args[4], ']')))
		}

	} else if ( 'custom' == args[3] ) {

		# Custom GO-mgmt
		rdata <- file.path('.', 'settings/go_mgmt.Rdata')
		if ( !file.exists(rdata) ) stop()
		load(rdata)

		attr.tables <- nm$graph.list.to.attr.tables(l)
		g <- nm$attr.tables.to.graph(attr.tables$nodes, attr.tables$edges)
		eval(parse(text=paste0('V(g)$', 'go', ' <- NA')))
		for (i in 1:vcount(g)) {
			eval(parse(text=paste0('V(g)[i]$', 'go', ' <- go.list[V(g)[i]$', args[4], ']')))
		}

	}

	my.symbols <- eval(parse(text=paste0('V(g)$', args[4])))
	my.regions <- getBM(
		c("hgnc_symbol", "chromosome_name", "start_position", "end_position", "band"),
		filters = c("hgnc_symbol", "chromosome_name"),
		values = list(
			hgnc_symbol=my.symbols,
			chromosome_name=normal.chroms
		),
		mart = ensembl
	)
	for (i in 1:vcount(g)) {
		row.id <- which(my.regions$hgnc_symbol == eval(parse(text=paste0('V(g)[i]$', args[4]))))
		if ( !is.null(row.id) ) {
			row <- my.regions[row.id, ]
			if ( 0 != nrow(row) ) {
				print(nrow(row))
				print(row)
				V(g)[i]$chromosome_name <- row$chromosome_name
				V(g)[i]$start_position <- row$start_position
				V(g)[i]$end_position <- row$end_position
				V(g)[i]$band <- row$band
			}
		}
	}

	graph.list <- nm$graph.to.list(g)
	write(toJSON(graph.list), paste0(args[2], '.json'))
}