#!/usr/bin/env Rscript
source('../extendIgraph.R')

options(echo=TRUE)
args <- commandArgs(trailingOnly = TRUE)
print(args)

setwd(paste0('/home/gire/public_html/SOGI/CJ/session/', args[1], '/'))

cat('Reading GRAPHML file.\n')
g <- read.graph(paste0(args[2], '.graphml'), format='graphml')
cat('Writing JSON file.\n')
write.graph(g, paste0(args[2], '.json'), format='json')
cat('Converted.\n')
