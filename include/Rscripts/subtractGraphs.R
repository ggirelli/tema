#!/usr/bin/env Rscript

source('../GraphManager.class.R')

options(echo=TRUE)
args <- commandArgs(trailingOnly = TRUE)

if(file.exists(paste0('/home/gire/public_html/SOGI/session/', args[1], '/'))) {
	setwd(paste0('/home/gire/public_html/SOGI/session/', args[1], '/'))

	if(file.exists(paste0(args[2], '.graphml')) && file.exists(paste0(args[3], '.graphml'))) {
		cat('Reading GRAPHML file.\n')
		gone <- read.graph(paste0(args[2], '.graphml'), format='graphml')
		gtwo <- read.graph(paste0(args[3], '.graphml'), format='graphml')
		cat('Subtracting.\n')
		gend <- GraphManager()$subtract(gtwo, gone)
		write.graph(gend, paste0(args[4], '.graphml'), format='graphml')
		cat('Done')
	}
}