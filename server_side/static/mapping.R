# BUILD GOA-MAPPING LIST #

rows <- scan('goa', 'raw', sep='\n')

l<-lapply(rows, FUN=function(row) { cols <- unlist(strsplit(row, '\t')); return(cols[c(3,5)]) })
m <- matrix(unlist(l), ncol=2, byrow=T)
to.rm <- union(which(is.na(m[,1])), which(is.na(m[,2])))
if ( 0 != length(to.rm) ) m <- m[-to.rm,]
m <- matrix(unlist(strsplit(unique(paste0(m[,1],'~',m[,2])),'~')), ncol=2, byrow=T)

goa.list <- lapply(unique(m[,1]), FUN=function(g, m) { return(m[g == m[,1], 2]) }, m=m)
names(goa.list) <- unique(m[,1])

# BUILD GOB-MAPPING LIST #

rows <- scan('gob', 'raw', sep='\n')

s <- lapply(1:length(rows), FUN=function(i, rows) {
	row <- rows[i]
	if ( '[Term]' == row ) return(c(unlist(strsplit(rows[i+1], ': '))[2], unlist(strsplit(rows[i+2], ': '))[2]))
}, rows=rows)
m <- matrix(unlist(s), ncol=2, byrow=T)

gob.list <- as.list(m[,2])
names(gob.list) <- m[,1]

# BUILD GO-MAPPING #

go.list <- lapply(goa.list, FUN=function(goa, gob.list) {
	return(unlist(gob.list[goa]))
}, gob.list=gob.list)

save('goa.list', 'gob.list', 'go.list', file='go_mgmt.Rdata')