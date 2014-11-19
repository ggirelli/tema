#!/usr/bin/env Rscript

options(echo=TRUE)
args <- commandArgs(trailingOnly = TRUE)

# Check parameters
if(length(args) != 1) stop('./parseSIF.R session_id')

# Load requirements
library(rjson)
source('extendIgraph.R')

# Start
wd <- paste0('../session/', args[1], '/')
if(file.exists(wd)) {
	setwd(wd)

	sif <- read.table('sif.dat', header=T)
	for (i in 1:ncol(sif)) {
		colnames(sif)[i] <- gsub("[.]", "_", colnames(sif)[i])
	}
	write(toJSON(data.frame(sif)), file.path('.', 'sif.json'))

}