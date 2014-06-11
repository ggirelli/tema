library('igraph')

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
		if(length(which(vapply(attr.value.x, FUN=function(x,y) { return(x %in% unlist(y)) }, FUN.VALUE=c(logical(1), logical(0)), y=attr.value.y))) == 0) return(FALSE)
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
			if(length(which(vapply(attr.value.x, FUN=function(x,y) { return(x %in% unlist(y)) }, FUN.VALUE=c(logical(1), logical(0)), y=attr.value.y))) == 0) return(FALSE)
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

get.vertex.attributes = function(v, id=FALSE) {
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

	# Add id column name
	if(id) {
		t <- cbind(1:length(t[,1]), t)
		colnames(t)[1] <- 'id'
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

get.edge.attributes = function(e, id=FALSE, path=FALSE) {
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

	# Add id column name
	if(id) {
		t <- cbind(1:length(t[,1]), t)
		colnames(t)[1] <- 'id'
	}

	# Add source/target
	if(path) {
		el <- get.edgelist(get('graph', attr(e, 'env')), names=FALSE)
		t <- cbind(t, paste0('n', el[,1]), paste0('n', el[,2]))
		colnames(t)[(length(t[1,])-1):length(t[1,])] <- c('source', 'target')
	}
	
	# Terminate
	return(t)
}

# Class to manage graphml graphs and perform graph operations
GraphManager <- function() {

	# Instantiate Graph Manager
	gm <- list(

		#------------#
		# ATTRIBUTES #
		#------------#



		#-----------#
		# FUNCTIONS #
		#-----------#
		
		# Transform
		#-----------------------------------

		undirected.noAttr = function(g) {
			# Transforms an DIRECTED graph into a UNDIRECTED one
			# Disregards edges/vertices attributes
			# 
			# Args:
			# 	g: undirected graph
			# 
			# Returns:
			# 	The UNDIRECTED graph
			
			if(!is.directed(g)) return(F)
			
			# Create undirected empty graph
			gf <- graph.empty(directed=T)

			# Add vertices
			gf <- gf + vertices(paste0(V(g)$name, '~IN'))
			gf <- gf + vertices(paste0(V(g)$name, '~OUT'))

			# Add edges
			el <- get.edgelist(g)
			gf <- gf + edges(c(t(cbind(paste0(el[,1], '~OUT'), paste0(el[,2], '~IN')))))

			# Remove 0-degree vertices
			gf <- delete.vertices(gf, V(gf)[which(degree(gf, V(gf)) == 0)])

			# Return undirected graph
			return(gf)
		},

		undirected = function(g) {
			# Transforms an DIRECTED graph into a UNDIRECTED one
			# Keeps edges/vertices attributes
			# 
			# Args:
			# 	g: undirected graph
			# 
			# Returns:
			# 	The UNDIRECTED graph
			
			if(!is.directed(g)) return(F)
			
			# Create undirected empty graph
			gf <- graph.empty(directed=T)

			# Add vertices
			gf <- gf + vertices(paste0(V(g)$name, '~IN'))
			gf <- gf + vertices(paste0(V(g)$name, '~OUT'))

			# Add vertices attributes
			attr.list <- list.vertex.attributes(g)
			for(attr.name in attr.list[which(attr.list != 'name')]) {
				eval(parse(text=paste0('V(gf)[1:length(V(g))]$', attr.name, ' <- V(g)$', attr.name)))
				eval(parse(text=paste0('V(gf)[length(V(g))+1:length(V(gf))]$', attr.name, ' <- V(g)$', attr.name)))
			}

			# Add edges
			el <- get.edgelist(g)
			gf <- gf + edges(c(t(cbind(paste0(el[,1], '~OUT'), paste0(el[,2], '~IN')))))

			# Add edges attributes
			attr.list <- list.edge.attributes(g)
			for(attr.name in attr.list[which(attr.list != 'name')]) eval(parse(text=paste0('E(gf)$', attr.name, ' <- E(g)$', attr.name)))

			# Remove 0-degree vertices
			gf <- delete.vertices(gf, V(gf)[which(degree(gf, V(gf)) == 0)])

			# Return undirected graph
			return(gf)
		},

		rename.vertex.attr = function(g, old.name, new.name) {
			# Changes the name of a certain vertex attribute
			# 
			# Args:
			# 	g: graph
			# 	old.name
			# 	new.name
			# 
			# Returns:
			# 	Updated graph
			
			eval(parse(text=paste0('V(g)$', new.name, ' <- V(g)$', old.name)))
			g <- remove.vertex.attribute(g, old.name)
			return(g)
		},

		rename.vertex.attributes = function(g, map) {
			# Changes the name of a given set of vertex attribute
			# 
			# Args:
			# 	g: graph
			# 	old.name
			# 	new.name
			# 
			# Returns:
			# 	Updated graph
			
			for(old in names(map)) g <- gm$rename.vertex.attr(g, old, eval(parse(text=paste0('map$', old))))
			return(g)
		},

		rename.edge.attr = function(g, old.name, new.name) {
			# Changes the name of a certain edge attribute
			# 
			# Args:
			# 	g: graph
			# 	map: list with old.name => new.name
			# 
			# Returns:
			# 	Updated graph
			
			eval(parse(text=paste0('E(g)$', new.name, ' <- E(g)$', old.name)))
			g <- remove.edge.attribute(g, old.name)
			return(g)
		},

		rename.edge.attributes = function(g, map) {
			# Changes the name of a given set edge attribute
			# 
			# Args:
			# 	g: graph
			# 	map: list with old.name => new.name
			# 
			# Returns:
			# 	Updated graph
			
			for(old in names(map)) g <- gm$rename.edge.attr(g, old, eval(parse(text=paste0('map$', old))))
			return(g)
		},

		rename.attributes = function(g, vertex.map=list(), edge.map=list()) {
			# Changes the name of a given set of edge and/or vertex attributes
			# 
			# Args:
			# 	g: graph
			# 	vertex.map: list with old.name => new.name of vertex attributes
			# 	edge.map: list with old.name => new.name of edge attributes
			# 	
			# Returns:
			# 	Updated graph

			if(vertex.map != list()) g <- gm$rename.vertex.attributes(g, vertex.map)
			if(edge.map != list()) g <- gm$rename.edge.attributes(g, edge.map)
			return(g)
		},

		# Measures
		#-----------------------------------
		
		clusteringCoefficient = function(v, env, graph) {
			# Calculates clustering coefficient of a certain vertex in a given graph
			# 
			# Args:
			# 	v: vertex
			# 	env: vertex environment
			# 	graph: vertex-containing graph
			# 
			# Return:
			# 	Clustering coefficient

			# Prepare vertex
			class(v) <- 'igraph.vs'; attr(v, 'env') <- env

			# Retrieve neighbors
			neigh <- neighbors(graph, v, mode='all')

			# Return 0 if less than 2 neighbors
			if(length(neigh) < 2) return(0)

			# Retrieve subgraph
			sg <- as.undirected(induced.subgraph(graph, neigh))

			# Calculate clustering coefficient
			cc <- 2 * ecount(sg) / (vcount(sg) * (vcount(sg) - 1))

			# Terminate
			return(cc)
		},

		clusteringCoefficients = function(g) {
			# Calculates the clustering coefficient of the given graph
			# 
			# Args:
			# 	g: graph
			# 
			# Returns
			# 	The clustering coefficient

			# Calculates clustering coefficient for each node
			c.list <- sapply(V(g), FUN=function(v, env, graph) {
				return(gm$clusteringCoefficient(v, env, graph))
			}, env=attr(V(g), 'env'), graph=g)

			# Terminate
			return(mean(c.list))
		},

		# Compare
		#-----------------------------------

		calcHammingDist = function(g.one, g.two) {
			# Calculates the Hamming (edit) distance between two UNDIRECTED graphs
			#
			# Args:
			#	g.one: first graph
			#	g.two: second graph
			#
			# Returns:
			#	The Hamming distance H(g.one,g.two)

			# Get edges
			el.one <- get.edgelist(g.one)
			el.two <- get.edgelist(g.two)

			# Get number of common edges
			common <- length(intersect(paste0(el.one[,1], '~', el.one[,2]), paste0(el.two[,1], '~', el.two[,2])))
			common <-  common + length(intersect(paste0(el.one[,2], '~', el.one[,1]), paste0(el.two[,1], '~', el.two[,2])))

			# Not normalized distance
			dH.raw <- (length(el.one[,1]) + length(el.two[,1])) - (2 * common)

			# Normalize distance
			max.v <- max(length(V(g.one)), length(V(g.two)))
			K <- (max.v * (max.v - 1))
			K <- K / 2
			dH <- dH.raw / K

			# Return distance
			return(dH)
		},

		calcIpsenDist = function(g.one, g.two, gamma) {
			# Calculates the Ipsen-Mikhailov (spectral) distance between two UNDIRECTED graphs
			#
			# Args:
			#	g.one: first graph
			#	g.two: second graph
			#	gamma: parameter corresponding to the HWHM of the calculated Lorentz distributions
			#
			# Returns:
			#	The Ipsen-Mikhailov distance IM(g.one,g.two)
			
			# Read graphs
			gs <- list(g.one, g.two)

			# SpectralDensity function
			specDens = function(omega, omegadef, gamma) {
				k <- 0
				for(i in 2:length(omegadef)) k = k + (gamma / ((omega - omegadef[i])^2 + gamma^2))
				return(k)
			}
			# Normalization constant
			sdK = function(omegadef, gamma) {
				return(integrate(specDens, lower=0, upper=Inf, omegadef=omegadef, gamma=gamma, stop.on.error = FALSE)$value)
			}
			# Normalized spectral density (rho[omega])
			rhoO = function(omega, omegadef, gamma, k) {
				return(specDens(omega, omegadef, gamma) / k)
			}

			# Prepare graphs data
			gs.data <- list()
			for(i in 1:length(gs)) {
				g <- gs[[i]]
				g.data <- list()
				g.data$g <- g

				# Get adjacency matrix
				g.data$adj <- as.matrix(get.adjacency(g))

				# Build laplacian matrix
				g.data$lap <- - g.data$adj
				for(i in seq(length(g.data$adj[,1]))) {
					g.data$lap[i,i] <- g.data$lap[i,i] + degree(g, V(g)[i])
				}

				# Get 'defined' frequencies
				g.data$eigva <- round(sort(eigen(g.data$lap)$values),5)
				g.data$freqs <- sqrt(abs(g.data$eigva))

				# Calculate K
				g.data$k <- sdK(g.data$freqs, gamma)

				# Return graph data
				gs.data <- append(gs.data, list(g.data))
			}

			# IM distance
			sdDiffSq = function(omega, one.omegadef, one.k, two.omegadef, two.k, gamma) {
				return((rhoO(omega, one.omegadef, gamma, one.k) - rhoO(omega, two.omegadef, gamma, two.k))**2)
			}
			dIM <- sqrt(integrate(sdDiffSq, lower=0, upper=Inf, one.omegadef=gs.data[[1]]$freqs, one.k=gs.data[[1]]$k, two.omegadef=gs.data[[2]]$freqs, two.k=gs.data[[2]]$k, gamma=gamma)$value)

			# Return Ipsen-Mikhailov distance
			return(dIM)
		},

		calcHIMDist = function(g.one, g.two, gamma, xi) {
			# Calculates the Ipsen-Mikhailov (spectral) distance between two UNDIRECTED graphs
			#
			# Args:
			#	g.one: first graph
			#	g.two: second graph
			#	gamma: parameter corresponding to the HWHM of the calculated Lorentz distributions in IM distance calculation
			#	xi: parameter corresponding to the weight of dIM over dH in the final distance
			#
			# Returns:
			#	The Ipsen-Mikhailov distance IM(g.one,g.two)
			
			dH <- gm$calcHammingDist(g.one, g.two)
			dIM <- gm$calcIpsenDist(g.one, g.two, gamma)
			dHIM <- (1/sqrt(1+xi)) * sqrt(dH**2 + xi * dIM**2)
			return(dHIM)
		},

		# Operations
		#-----------------------------------

		merge = function(g.one, g.two, vertex.key.label='name', vertex.attr.comb=getIgraphOpt("vertex.attr.comb"), edge.attr.comb=getIgraphOpt("edge.attr.comb")) {
			# Identify the 'bigger' graph
			if(vcount(g.one) > vcount(g.two)) {

				#-------#
				# NODES #
				#-------#

				if(length(list.vertex.attributes(g.two)) != 0) {
					# Retrieve vertex attributes list
					attrs <- get.vertex.attributes(V(g.two))

					# Prepare string for attributes assignment
					attr.names <- colnames(attrs)
					if('id' == attr.names) attr.names <- attr.names[which(attr.names != vertex.key.label)]

					attr.list <- list()
					s <- ''
					for(attr in attr.names) {
						attr.list[[attr]] <- get.vertex.attr(attr, V(g.two))
						s <- paste0(s, ', ', attr, '=attr.list$', attr)
					}
				} else {
					s <- ''
				}

				# Assign attributes
				eval(parse(text=paste0('g.one <- g.one + vertices(V(g.two)$', vertex.key.label, s, ')')))

				#-------#
				# EDGES #
				#-------#

				if(length(list.edge.attributes(g.two)) != 0) {
					# Retrieve edge attributes list
					attrs <- get.edge.attributes(E(g.two))

					# Prepare string for attributes assignment
					attr.list <- list()
					s <- ''
					for(attr in colnames(attrs)) {
						attr.list[[attr]] <- get.edge.attr(attr, E(g.two))
						s <- paste0(s, ', ', attr, '=attr.list$', attr)
					}
				} else {
					s <- ''
				}
				
				# Assign attributes
				edge.list <- c(t(get.edgelist(g.two)))
				eval(parse(text=paste0('g.one <- g.one + edges(edge.list', s, ')')))

				#----------#
				# SIMPLIFY #
				#----------#
				
				vkeys <- eval(parse(text=paste0('V(g.one)$', vertex.key.label)))
				vkeys.uniq <- unique(eval(parse(text=paste0('V(g.one)$', vertex.key.label))))
				vids <- matrix(1:length(vkeys.uniq), nrow=1)
				colnames(vids) <- vkeys.uniq

				g.one <- contract.vertices(g.one, vids[,vkeys], vertex.attr.comb=vertex.attr.comb)
				g.one <- simplify(g.one, remove.loops=F, edge.attr.comb=edge.attr.comb)

				# Terminate
				return(g.one)
			} else {
				return(gm$merge(g.two, g.one))
			}
		},

		subtract = function(g.one, g.two) {
			# Removes edges and nodes that are common to both graphs from the first graph
			
			common.vertices <- which(V(g.one) %in% V(g.two))
			if(length(common.vertices) != 0 ) g.one <- g.one - vertices(V(g.one)[common.vertices])

			common.edges <- which(E(g.one) %in% E(g.two))
			if(length(common.edges) != 0) g.one <- delete.edges(g.one, E(g.one)[common.edges])

			return(g.one)
		},

		intersect = function(g.one, g.two) {
			# Intersects edges and nodes
			
			uncommon.vertices <- which(!(V(g.one) %in% V(g.two)))
			if(length(uncommon.vertices) != 0 ) g.one <- g.one - vertices(V(g.one)[uncommon.vertices])

			uncommon.edges <- which(!(E(g.one) %in% E(g.two)))
			if(length(uncommon.edges) != 0) g.one <- delete.edges(g.one, E(g.one)[uncommon.edges])

			return(g.one)
		},

		contains = function(g.one, g.two) {
			# Verifies if g.one contains g.two
			
			if(length(which(!(V(g.two) %in% V(g.one)))) != 0) return(FALSE)
			if(length(which(!(E(g.two) %in% E(g.one)))) != 0) return(FALSE)

			return(TRUE)
		},

		contained = function(g.one, g.two) {
			# Verifies if g.two contains g.one
			return(gm$contains(g.two, g.one))
		}

	)

	# Assign class attribute
	class(gm) <- 'GraphManager'

	# Return instantiaded Graph Manager
	return(gm)
}
