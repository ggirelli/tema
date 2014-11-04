library('igraph')


# Comparison extension
# -----------------------------

"==.igraph.vs" = function(x, y) {
	# Can compare a couple of vertices, or a vertex to a vertex vector.
	# The comparison is carried out on the attributes of each vertex (except for the id).
	
	# If both node lists have length == 1 perform pair comparison
	if(length(x) == 1 && length(y) == 1) {
		
		# Retrieve attribute name list
		attr.list.x <- list.vertex.attributes(get('graph', attr(x, 'env')))
		attr.list.y <- list.vertex.attributes(get('graph', attr(y, 'env')))
		
		if(length(attr.list.x) == 0 | length(attr.list.y) == 0) {
			cat('Error: graphs have no node attributes.','\n')
			return(FALSE)
		}

		# Check attribute name list
		if(length(attr.list.x) != length(attr.list.y)) return(FALSE)
		if(length(which(attr.list.x %in% attr.list.y)) != length(attr.list.x)) return(FALSE)

		# Retrieve attribute values
		attr.value.x <- lapply(attr.list.x, x, FUN=function(attr, x) { return(eval(parse(text=paste0('x$', attr)))) })
		attr.value.y <- lapply(attr.list.y, y, FUN=function(attr, y) { return(eval(parse(text=paste0('y$', attr)))) })
		
		# Verify identity
		if(length(which(vapply(attr.value.x, FUN=function(x,y) { return(x %in% unlist(y)) }, FUN.VALUE=c(logical(1), logical(0)), y=attr.value.y))) != length(attr.list.x)) return(FALSE)
		return(TRUE)

	} else { # Other cases:

		if(length(x) == 1) { # x is singular while y is not
			return(vapply(y, FUN=function(x, y, env) {
				if(y == V(get('graph', env))[x]) return(TRUE)
				return(FALSE)
			}, FUN.VALUE=c(logical(1),logical(0)), y=x, env=attr(y, 'env')))
		} else if(length(y) == 1) { # y is singular while x is not
			cat('Error: cannot compare node vector to single node.\n')
			return(NULL)
		} else {
			# Both x and y are note singular
			# They have different length
			if(length(x) != length(y)) {
				cat('Error: can compare only node vectors with the same length.')
				return(NULL)
			}
			# They have the same length
			cat('Compared node vectors in a pair-wise fashion.\n')
			return(vapply(x, FUN=function(x, y, env) {
				if(V(get('graph', env))[x] == y[x]) return(TRUE)
				return(FALSE)
			}, FUN.VALUE=c(logical(1),logical(0)), y=y, env=attr(x, 'env')))
		}
	}
}

"==.igraph.es" = function(x, y) {
	# Can compare a couple of edges, or an edge to an edge vector.
	# The comparison is carried out on the attributes of each edge  and, obiously, also on target/source.
	
	# If both edge lists have length == 1 perform pair comparison
	if(length(x) == 1 && length(y) == 1) {

		# Retrieve attribute name list
		attr.list.x <- list.edge.attributes(get('graph', attr(x, 'env')))
		attr.list.y <- list.edge.attributes(get('graph', attr(y, 'env')))
		
		# Check attribute name list
		if(length(attr.list.x) != length(attr.list.y)) return(FALSE)
		if(length(which(attr.list.x %in% attr.list.y)) != length(attr.list.x)) return(FALSE)

		# Retrieve target/source list
		edge.list.x <- get.edgelist(get('graph', attr(x, 'env')))[as.numeric(x),]
		edge.list.y <- get.edgelist(get('graph', attr(y, 'env')))[as.numeric(y),]

		# Check target/source list
		if(!identical(edge.list.x, edge.list.y)) return(FALSE)

		# If there are edge attributes, check their value
		if(length(attr.list.x) != 0) {
			# Retrieve attribute values
			attr.value.x <- lapply(attr.list.x, x, FUN=function(attr, x) { return(eval(parse(text=paste0('x$', attr)))) })
			attr.value.y <- lapply(attr.list.y, y, FUN=function(attr, y) { return(eval(parse(text=paste0('y$', attr)))) })

			# Verify identity
			if(length(which(vapply(attr.value.x, FUN=function(x,y) { return(x %in% unlist(y)) }, FUN.VALUE=c(logical(1), logical(0)), y=attr.value.y))) != length(attr.list.x)) return(FALSE)
			return(TRUE)
		} else {
			return(TRUE)
		}

	} else { # Other cases:

		if(length(x) == 1) { # x is singular while y is not
			return(vapply(y, FUN=function(x, y, env) {
				if(y == E(get('graph', env))[x]) return(TRUE)
				return(FALSE)
			}, FUN.VALUE=c(logical(1),logical(0)), y=x, env=attr(y, 'env')))
		} else if(length(y) == 1) { # y is singular while x is not
			cat('Error: cannot compare edge vector to single edge.\n')
			return(NULL)
		} else {
			# Both x and y are note singular
			# They have different length
			if(length(x) != length(y)) {
				cat('Error: can compare only edge vectors with the same length.')
				return(NULL)
			}
			# They have the same length
			cat('Compared edge vectors in a pair-wise fashion.\n')
			return(vapply(x, FUN=function(x, y, env) {
				if(E(get('graph', env))[x] == y[x]) return(TRUE)
				return(FALSE)
			}, FUN.VALUE=c(logical(1),logical(0)), y=y, env=attr(x, 'env')))
		}
	}
}

"%in%" = function(x, y) {
	# Extended to allow operations with igraph.vs (nodes)
	
	if(class(x) == class(y) && class(x) == 'igraph.vs') {
		if(length(y) == 0 || length(x) == 0) {
			cat('Error: cannot compare NULL\n')
			return(NULL)
		}
		if(length(x) == 1 && length(y) == 1) return(x == y)
		if(length(x) == 1 && length(y) > 1) return(length(which(x == y)) != 0)
		if(length(y) == 1 && length(x) > 1) return(length(which(y == x)) != 0)
		if(length(y) > 1 && length(x) > 1) {
			# Retrieve attribute name list
			attr.list.x <- list.vertex.attributes(get('graph', attr(x, 'env')))
			attr.list.y <- list.vertex.attributes(get('graph', attr(y, 'env')))

			if(length(attr.list.x) == 0 | length(attr.list.y) == 0) {
				cat('Error: graphs have no node attributes.','\n')
				return(FALSE)
			}

			return(vapply(x, FUN=function(x, y, env) {
				class(x) <- 'igraph.vs'; attr(x, 'env') <- env
				class(y) <- 'igraph.vs'
				return(x %in% y)
			}, FUN.VALUE=c(logical(0), logical(1)), y=y, env=attr(x, 'env')))
		}
		cat("Error: wrong lengths\n")
		return(NULL)
	}

	if(class(x) == class(y) && class(x) == 'igraph.es') {
		if(length(y) == 0 || length(x) == 0) {
			cat('Error: cannot compare NULL\n')
			return(NULL)
		}
		if(length(x) == 1 && length(y) == 1) return(x == y)
		if(length(x) == 1 && length(y) > 1) return(length(which(x == y)) != 0)
		if(length(y) == 1 && length(x) > 1) return(length(which(y == x)) != 0)
		if(length(y) > 1 && length(x) > 1) {
			return(vapply(x, FUN=function(x, y, env) {
				class(x) <- 'igraph.es'; attr(x, 'env') <- env
				class(y) <- 'igraph.es'
				return(x %in% y)
			}, FUN.VALUE=c(logical(0), logical(1)), y=y, env=attr(x, 'env')))
		}
		cat("Error: wrong lengths\n")
		return(NULL)
	}

	# Old %in%
	match(x, y, nomatch = 0L) > 0L
}


# Comparison functions
# -----------------------------

# Equal to "==.igraph.vs"
compare.vertices.couple = function(x, y) { return(x == y) }

compare.vertices.couple.skipping = function(x, y, skip=c()) {
	# Can compare a couple of vertices, or a vertex to a vertex vector.
	# The comparison is carried out on the attributes of each vertex (except for the id).
	# The attributes specified in skip are skipped
	
	if(0 == length(skip)) { return(compare.vertices.couple(x, y)) }
	
	# If both node lists have length == 1 perform pair comparison
	if(length(x) == 1 && length(y) == 1) {

		# Retrieve attribute name list
		attr.list.x <- list.vertex.attributes(get('graph', attr(x, 'env')))
		attr.list.x <- unlist(sapply(attr.list.x, FUN=function(x, skip) { if(!(x %in% skip)) { return(x) } }, skip=skip))
		attr.list.y <- list.vertex.attributes(get('graph', attr(y, 'env')))
		attr.list.y <- unlist(sapply(attr.list.y, FUN=function(x, skip) { if(!(x %in% skip)) { return(x) } }, skip=skip))
		
		if(length(attr.list.x) == 0 | length(attr.list.y) == 0) {
			cat('Error: graphs have no node attributes.','\n')
			return(FALSE)
		}

		# Check attribute name list
		if(length(attr.list.x) != length(attr.list.y)) return(FALSE)
		if(length(which(attr.list.x %in% attr.list.y)) != length(attr.list.x)) return(FALSE)

		# Retrieve attribute values
		attr.value.x <- lapply(attr.list.x, x, FUN=function(attr, x) { return(eval(parse(text=paste0('x$', attr)))) })
		attr.value.y <- lapply(attr.list.y, y, FUN=function(attr, y) { return(eval(parse(text=paste0('y$', attr)))) })

		# Verify identity
		if(length(which(vapply(attr.value.x, FUN=function(x,y) { return(x %in% unlist(y)) }, FUN.VALUE=c(logical(1), logical(0)), y=attr.value.y))) != length(attr.list.x)-1) return(FALSE)
		return(TRUE)

	} else { # Other cases:

		if(length(x) == 1) { # x is singular while y is not
			return(vapply(y, FUN=function(x, y, env, skip) {
				if(compare.vertices.couple.skipping(y, V(get('graph', env))[x], skip)) return(TRUE)
				return(FALSE)
			}, FUN.VALUE=c(logical(1),logical(0)), y=x, env=attr(y, 'env'), skip=skip))
		} else if(length(y) == 1) { # y is singular while x is not
			cat('Error: cannot compare node vector to single node.\n')
			return(NULL)
		} else {
			# Both x and y are note singular
			# They have different length
			if(length(x) != length(y)) {
				cat('Error: can compare only node vectors with the same length.')
				return(NULL)
			}
			# They have the same length
			cat('Compared node vectors in a pair-wise fashion.\n')
			return(vapply(x, FUN=function(x, y, env, skip) {
				if(compare.vertices.couple.skipping(V(get('graph', env))[x], y[x], skip)) return(TRUE)
				return(FALSE)
			}, FUN.VALUE=c(logical(1),logical(0)), y=y, env=attr(x, 'env'), skip=skip))
		}
	}
}

# From "%in%"
compare.vertices.list = function(x, y) { return(x %in% y) }

compare.vertices.list.skipping = function(x, y, skip=c()) {
	if(length(y) == 0 || length(x) == 0) {
		cat('Error: cannot compare NULL\n')
		return(NULL)
	}
	if(length(x) == 1 && length(y) == 1) return(compare.vertices.couple.skipping(x, y, skip))
	if(length(x) == 1 && length(y) > 1) return(length(which(compare.vertices.couple.skipping(x, y, skip))) != 0)
	if(length(y) == 1 && length(x) > 1) return(length(which(compare.vertices.couple.skipping(y, x, skip))) != 0)
	if(length(y) > 1 && length(x) > 1) {
		# Retrieve attribute name list
		attr.list.x <- list.vertex.attributes(get('graph', attr(x, 'env')))
		attr.list.x <- unlist(sapply(attr.list.x, FUN=function(x, skip) { if(!(x %in% skip)) { return(x) } }, skip=skip))
		attr.list.y <- list.vertex.attributes(get('graph', attr(y, 'env')))
		attr.list.y <- unlist(sapply(attr.list.y, FUN=function(x, skip) { if(!(x %in% skip)) { return(x) } }, skip=skip))

		if(length(attr.list.x) == 0 | length(attr.list.y) == 0) {
			cat('Error: graphs have no node attributes.','\n')
			return(FALSE)
		}

		return(vapply(x, FUN=function(x, y, env, skip) {
			class(x) <- 'igraph.vs'; attr(x, 'env') <- env
			class(y) <- 'igraph.vs'
			return(compare.vertices.list.skipping(x, y, skip))
		}, FUN.VALUE=c(logical(0), logical(1)), y=y, env=attr(x, 'env'), skip=skip))
	}
	cat("Error: wrong lengths\n")
	return(NULL)
}

# Equal to "==.igraph.es"
compare.edges.couple = function(x, y) { return(x == y) }

compare.edges.couple.skipping = function(x, y, skip=c()) {
	# Can compare a couple of edges, or an edge to an edge vector.
	# The comparison is carried out on the attributes of each edge  and, obiously, also on target/source.
	# The attributes specified in skip are skipped
	
	if(0 == length(skip)) { return(compare.edges.couple(x, y)) }
	
	# If both edge lists have length == 1 perform pair comparison
	if(length(x) == 1 && length(y) == 1) {

		# Retrieve attribute name list
		attr.list.x <- list.edge.attributes(get('graph', attr(x, 'env')))
		attr.list.x <- unlist(sapply(attr.list.x, FUN=function(x, skip) { if(!(x %in% skip)) { return(x) } }, skip=skip))
		attr.list.y <- list.edge.attributes(get('graph', attr(y, 'env')))
		attr.list.y <- unlist(sapply(attr.list.y, FUN=function(x, skip) { if(!(x %in% skip)) { return(x) } }, skip=skip))
		
		# Check attribute name list
		if(length(attr.list.x) != length(attr.list.y)) return(FALSE)
		if(length(which(attr.list.x %in% attr.list.y)) != length(attr.list.x)) return(FALSE)

		# Retrieve target/source list
		edge.list.x <- get.edgelist(get('graph', attr(x, 'env')))[as.numeric(x),]
		edge.list.y <- get.edgelist(get('graph', attr(y, 'env')))[as.numeric(y),]

		# Check target/source list
		if(!identical(edge.list.x, edge.list.y)) return(FALSE)

		# If there are edge attributes, check their value
		if(length(attr.list.x) != 0) {
			# Retrieve attribute values
			attr.value.x <- lapply(attr.list.x, x, FUN=function(attr, x) { return(eval(parse(text=paste0('x$', attr)))) })
			attr.value.y <- lapply(attr.list.y, y, FUN=function(attr, y) { return(eval(parse(text=paste0('y$', attr)))) })

			# Verify identity
			if(length(which(vapply(attr.value.x, FUN=function(x,y) { return(x %in% unlist(y)) }, FUN.VALUE=c(logical(1), logical(0)), y=attr.value.y))) == length(attr.list.x)-1) return(FALSE)
			return(TRUE)
		} else {
			return(TRUE)
		}

	} else { # Other cases:

		if(length(x) == 1) { # x is singular while y is not
			return(vapply(y, FUN=function(x, y, env, skip) {
				if(compare.edges.couple.skipping(y, E(get('graph', env))[x], skip)) return(TRUE)
				return(FALSE)
			}, FUN.VALUE=c(logical(1),logical(0)), y=x, env=attr(y, 'env'), skip=skip))
		} else if(length(y) == 1) { # y is singular while x is not
			cat('Error: cannot compare edge vector to single edge.\n')
			return(NULL)
		} else {
			# Both x and y are note singular
			# They have different length
			if(length(x) != length(y)) {
				cat('Error: can compare only edge vectors with the same length.')
				return(NULL)
			}
			# They have the same length
			cat('Compared edge vectors in a pair-wise fashion.\n')
			return(vapply(x, FUN=function(x, y, env, skip) {
				if(compare.edges.couple.skipping(E(get('graph', env))[x], y[x], skip)) return(TRUE)
				return(FALSE)
			}, FUN.VALUE=c(logical(1),logical(0)), y=y, env=attr(x, 'env'), skip=skip))
		}
	}
}

# From "%in%"
compare.edges.list = function(x, y) { return(x %in% y) }

compare.edges.list.skipping = function(x, y, skip=c()) {
		if(length(y) == 0 || length(x) == 0) {
			cat('Error: cannot compare NULL\n')
			return(NULL)
		}
		if(length(x) == 1 && length(y) == 1) return(compare.edges.couple.skipping(x, y, skip))
		if(length(x) == 1 && length(y) > 1) return(length(which(compare.edges.couple.skipping(x, y, skip))) != 0)
		if(length(y) == 1 && length(x) > 1) return(length(which(compare.edges.couple.skipping(y, x, skip))) != 0)
		if(length(y) > 1 && length(x) > 1) {
			return(vapply(x, FUN=function(x, y, env, skip) {
				class(x) <- 'igraph.es'; attr(x, 'env') <- env
				class(y) <- 'igraph.es'
				return(compare.edges.list.skipping(x, y, skip))
			}, FUN.VALUE=c(logical(0), logical(1)), y=y, env=attr(x, 'env'), skip=skip))
		}
		cat("Error: wrong lengths\n")
		return(NULL)
}


# Retrieve attributes
# -----------------------------

get.vertex.attr = function(name, v) {
	# Returns a certain attribute of a given vertex
	# 
	# Args:
	# 	name: attribute name
	# 	v: vertex
	# 	
	# Returns:
	# 	Attribute value
	
	return(eval(parse(text=paste0("V(get('graph', attr(v, 'env')))[v]$", name))))
}

get.vertex.attributes = function(v, skip=c('id')) {
	# Returns all the attributes of a given vertex
	# 
	# Args:
	# 	v: vertex
	# 	
	# Returns:
	# 	List of attributes values

	# Retrieve vertex attributes list
	vl <- list.vertex.attributes(get('graph', attr(v, 'env')))

	# Check for attributes
	if(length(vl) == 0) {
		cat('No attributes to be retrieved.', '\n')
		return(NULL)
	}

	# Prepare table
	t <- sapply(vl, FUN=function(name, v) { return(get.vertex.attr(name, v)) }, v=v)

	# Get t length
	if(!is.matrix(t)) {
		# Add id column name
		if(!('id' %in% skip)) {
			t <- c(1, t)
			names(t)[1] <- 'id'
		}
	} else {
		# Add id column name
		if(!('id' %in% skip)) {
			t <- cbind(1:length(t[,1]), t)
			colnames(t)[1] <- 'id'
		}
	}

	# Terminate
	return(t)
}

get.edge.attr = function(name, e) {
	# Returns a certain attribute of a given edge
	# 
	# Args:
	# 	name: attribute name
	# 	e: edge
	# 	
	# Returns:
	# 	Attribute value
	
	return(eval(parse(text=paste0("E(get('graph', attr(e, 'env')))[e]$", name))))
}

get.edge.attributes = function(e, skip=c('id','source','target')) {
	# Returns all the attributes of a given edge
	# 
	# Args:
	# 	e: edge
	# 	
	# Returns:
	# 	List of attributes values

	# Retrieve edge attributes list
	el <- list.edge.attributes(get('graph', attr(e, 'env')))

	# Check for attributes
	if(length(el) == 0) {
		cat('No attributes to be retrieved.', '\n')
		return(NULL)
	}

	# Prepare table
	t <- sapply(el, FUN=function(name, e) { return(get.edge.attr(name, e)) }, e=e)

	# Get t length
	if(!is.matrix(t)) {
		# Add id column name
		if(!('id' %in% skip)) {
			t <- c(1, t)
			names(t)[1] <- 'id'
		}

		# Add source/target
		el <- 0
		if(!('source' %in% skip)) {
			el <- get.edgelist(get('graph', attr(e, 'env')), names=FALSE)
			t <- c(t, paste0('n', el[,1]))
			names(t)[length(t)] <- c('source')
		}
		if(!('target' %in% skip)) {
			if(0 == el) el <- get.edgelist(get('graph', attr(e, 'env')), names=FALSE)
			t <- c(t, paste0('n', el[,2]))
			names(t)[length(t)] <- c('target')
		}
	} else {
		# Add id column name
		if(!('id' %in% skip)) {
			t <- cbind(1:length(t[,1]), t)
			colnames(t)[1] <- 'id'
		}

		# Add source/target
		el <- 0
		if(!('source' %in% skip)) {
			el <- get.edgelist(get('graph', attr(e, 'env')), names=FALSE)
			t <- cbind(t, paste0('n', el[,1]))
			colnames(t)[length(t[1,])] <- c('source')
		}
		if(!('target' %in% skip)) {
			if(0 == el) el <- get.edgelist(get('graph', attr(e, 'env')), names=FALSE)
			t <- cbind(t, paste0('n', el[,2]))
			colnames(t)[length(t[1,])] <- c('target')
		}
	}
	
	# Terminate
	return(t)
}


# Write output
# -----------------------------

write.graph.old = write.graph
write.graph = function(graph, file, format, ...) {
	if(format %in% c('json', 'JSON')) {
		write.graph.json(graph,file)
	} else {
		write.graph.old(graph, file, format)
	}
}

write.graph.json = function(graph, file, ...) {
	library('rjson')

	l <- list(nodes=list(), edges=list())

	# NODES
	val <- get.vertex.attributes(V(graph), skip=c())
	if(is.matrix(val)) {
		l$nodes <- apply(val, MARGIN=1, FUN=function(x, index) {
			data <- list(id=paste0('n', as.vector(x['id'])))
			for(attr in names(x)[which(names(x) != 'id')]) {
				data <- append(data, eval(parse(text=paste0('x[\'', attr, '\']'))))
			}
			return(list(data=data))
		})
	} else {
		data <- sapply(1:length(val), FUN=function(x, val) {
			if(names(val[x]) == 'id') return(list(id=paste0('e',val[x])))
			return(val[x])
		}, val=val)
		l$nodes <- list(c(list(data=data)))
	}

	# EDGES
	eal <- get.edge.attributes(E(graph), skip=c())
	if(is.matrix(eal)) {
		l$edges <- apply(eal, MARGIN=1, FUN=function(x, index) {
			data <- list(id=paste0('e', as.vector(x['id'])))
			for(attr in names(x)[which(names(x) != 'id')]) {
				data <- append(data, eval(parse(text=paste0('x[\'', attr, '\']'))))
			}
			return(list(data=data))
		})
	} else {
		data <- sapply(1:length(eal), FUN=function(x, eal) {
			if(names(eal[x]) == 'id') return(list(id=paste0('e',eal[x])))
			return(eal[x])
		}, eal=eal)
		l$edges <- list(c(list(data=data)))
	}

	write(toJSON(l), file=file.path(file))
}