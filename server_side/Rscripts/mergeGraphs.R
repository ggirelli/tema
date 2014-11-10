#!/usr/bin/env Rscript

source('GraphManager.class.R')

options(echo=TRUE)
args <- commandArgs(trailingOnly = TRUE)

if(file.exists(paste0('/home/gire/public_html/SOGI/session/', args[1], '/'))) {
	setwd(paste0('/home/gire/public_html/SOGI/session/', args[1], '/'))

	if(file.exists(paste0(args[2], '.graphml')) && file.exists(paste0(args[3], '.graphml'))) {
		cat('Reading GRAPHML file.\n')
		gone <- read.graph(paste0(args[2], '.graphml'), format='graphml')
		gtwo <- read.graph(paste0(args[3], '.graphml'), format='graphml')
		cat('Found vertex ID attribute.\n')
		vkey <- args[5]
		cat('Reading attribute combination options.\n')
		vatl <- eval(parse(text=args[6]))
		eatl <- eval(parse(text=args[7]))
		print(vatl)
		print(eatl)
		gend <- GraphManager()$merge(gone, gtwo, vertex.key.label=vkey, vertex.attr.comb=vatl, edge.attr.comb=eatl)
		write.graph(gend, paste0(args[4], '.graphml'), format='graphml')
		cat('done')
	}
}
