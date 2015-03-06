(function () {
    "use strict";

    define([], function () {

        return function (q, http, rootScope, filters, navigate) {
            var self = this;

            /**
             * Default network
             * @type {Object}
             */
            self.elements = {
                nodes: [
                    { data: { id: "n0", name: "Welcome", x: -100, y: -50 }, position: {x: -100, y: -50} },
                    { data: { id: "n1", name: "to", x: 100, y: 0 }, position: {x: 100, y: 0} },
                    { data: { id: "n2", name: "TEMA", x: -50, y: 100 }, position: {x: -50, y: 100} }
                ],
                edges: [
                    { data: { id: "e0", source: "n0", target: "n1" } },
                    { data: { id: "e1", source: "n1", target: "n2" } },
                    { data: { id: "e2", source: "n2", target: "n1" } }
                ]
            };

            self.current_data = {
                name: 'default'
            };
            self.current = self.elements;
            self.visualized = true;
            self.visualization = self.elements;
            self.filtered = undefined;

            self.filters = filters;
            self.navi = navigate;

            /**
             * Initialize cytoscape and load the default network
             */
            self.init = function () {
                // Initialize canvas
                window.cy = cytoscape({
                    container: document.getElementById('canvas'),
                    zoom: 1,
                    pan: { x: 0, y: 0 },
                    minZoom: 1e-50,
                    maxZoom: 1e50,
                    zoomingEnabled: true,
                    userPanningEnabled: true,
                    boxSelectionEnabled: true,
                    motionBlur: false,
                    layout: {
                        name: 'preset'
                    },
                    style: cytoscape.stylesheet()
                        .selector('node').css({
                            'background-color': 'white',
                            'border-color': '#909090',
                            'border-width': '1px',
                            'content': 'data(name)',
                            'text-valign': 'center',
                            'color': '#323232',
                            'min-zoomed-font-size': '10px',
                            'font-family': 'arial',
                            'text-outline-color': 'white',
                            'text-outline-width': '1'
                        })
                        .selector('edge').css({
                            'line-color': '#656565',
                            'target-arrow-color': '#656565',
                            'target-arrow-shape': 'triangle'
                        })
                        .selector(':selected').css({
                            'background-color': 'black',
                            'line-color': 'black',
                            'target-arrow-color': 'black',
                            'source-arrow-color': 'black'
                        })
                        .selector('.faded').css({
                            'opacity': 0.25,
                            'text-opacity': 0
                        }),

                    ready: function () {
                        var cy = this;

                        cy.on('tap', 'node', function(e){
                            var node = e.cyTarget; 
                            var neighborhood = node.neighborhood().add(node);

                            cy.elements().addClass('faded');
                            neighborhood.removeClass('faded');

                            // Broadcast to inspector
                            rootScope.$broadcast('inspect_node', node);
                        });

                        cy.on('tap', 'edge', function(e){
                            var edge = e.cyTarget;

                            cy.elements().addClass('faded');
                            edge.source().removeClass('faded');
                            edge.target().removeClass('faded');

                            // Broadcast to inspector
                            rootScope.$broadcast('inspect_edge', edge);
                        });

                        cy.on('tap', function(e){
                            if( e.cyTarget === cy ){
                                cy.elements().removeClass('faded');
                                rootScope.$broadcast('close_inspector');
                            }
                        });

                        cy.load(self.elements, undefined, undefined);
                    },
                });
            };

            /**
             * Loads a network in the canvas
             * @param  {Object} network    from networks.list
             * @param  {string} session_id
             */
            self.load = function (network, session_id) {

                var qwait = q.defer();

                http({

                    method: 'POST',
                    data: {
                        action: 'get_network',
                        id: session_id,
                        network_id: network.id
                    },
                    url: 's/'

                }).
                    success(function (data) {
                        if ( 0 == data['err'] ) {
                            self.current_data = network;
                            self.current = data.network;
                            self.filtered = undefined;
                            self.visualized = true;
                            self.visualization = $.extend(true, {}, data.network);
                            cy.load(data.network);
                        } else {
                            console.log(data);
                        }
                        qwait.resolve(data);
                    });

                return qwait.promise;
            };

            /**
             * Preloads a network in the service
             * @param  {Object} network    from networks.list
             * @param  {string} session_id
             */
            self.preload = function (network, session_id) {

                var qwait = q.defer();

                http({

                    method: 'POST',
                    data: {
                        action: 'get_network',
                        id: session_id,
                        network_id: network.id
                    },
                    url: 's/'

                }).
                    success(function (data) {
                        if ( 0 == data['err'] ) {
                            self.current_data = network;
                            self.current = data.network;
                            self.filtered = undefined;
                            self.visualized = false;
                            self.visualization = $.extend(true, {}, data.network);
                            cy.remove('*');
                        } else {
                            console.log(data);
                        }
                        qwait.resolve(data);
                    });

                return qwait.promise;
            };

            /**
             * Loades preloaded network in the canvas
             */
            self.postload = function () {
                cy.load(self.visualization);
                self.visualized = true;
            };

            /**
             * Applies the specified layout to the canvas
             * @param  {Object} params
             */
            self.layout = function (params) {
                cy.layout(params);
            };

            /**
             * Centers the current network in the canvas
             */
            self.center = function () {
                cy.center(cy.$('*'));
            };

            /**
             * @param  {String}  network_name
             * @return {Boolean}      if the network is loaded
             */
            self.isLoaded = function (network_name) {
                return self.current_data.name == network_name && self.visualized;
            };

            /**
             * @param  {String}  network_name
             * @return {Boolean}      if the network is preloaded
             */
            self.isPreloaded = function (network_name) {
                return self.current_data.name == network_name && !self.visualized;
            };

            /**
             * Saves current visualization on the server
             * @param  {string} session_id
             * @param  {Object} networks   network list
             */
            self.save = function (session_id, networks, layout) {
                var new_name = prompt('Insert the name for the new network:');
                var network;

                if ( null == new_name ) {
                    alert('A name is required to save the current visualization.');
                } else {
                    // Check if new_name is already in use
                    var checked = true;
                    for (var i = networks.length - 1; i >= 0; i--) {
                        if ( new_name == networks[i].name || null == new_name || '' == new_name ) {
                            checked = false;
                        }
                    }

                    if( !checked ) {
                        alert('Name already in use.');
                    } else {
                        // Save
                        
                        if ( 0 == Object.keys(cy.json().elements).length ) {
                            network = JSON.stringify({nodes:[], edges:{}});
                        } else {
                            network = JSON.stringify(cy.json().elements);
                        }

                        http({

                            method: 'POST',
                            data: {
                                action: 'save_network',
                                id: session_id,
                                network: network,
                                layout: layout,
                                name: new_name
                            },
                            url: 's/'

                        }).
                            success(function (data) {
                                rootScope.$broadcast('reload_network_list', session_id);
                            });
                    }
                }
            };

            /**
             * @param  {string} what 'edges' or 'nodes'
             * @return {list} attribute list
             * @return {null} if what is wrong
             */
            self.get_attributes = function (what) {
                if ( -1 != ['edges', 'nodes'].indexOf(what) ) {
                    if ( -1 != Object.keys(self.current).indexOf(what) ) {
                        if ( 0 != self.current[what].length ) {
                            return Object.keys(self.current[what][0].data);
                        }
                    }
                }
                return;
            };

            // Node reserved attributes
            self.node_attr_reserved = ['id', 'name', 'x', 'y'];
            // Edge reserved attributes
            self.edge_attr_reserved = ['id','source','target'];

            /**
             * @param  {String}  what 'edges' or 'nodes'
             * @param  {String}  attr label
             * @return {Boolean}      if it is a reserved attributed
             */
            self.is_attr_reserved = function (what, attr) {
                if ( -1 != ['edges', 'nodes'].indexOf(what) ) {
                    if ( 'nodes' == what ) {
                        return -1 != self.node_attr_reserved.indexOf(attr);
                    } else if ( 'edges' == what) {
                        return -1 != self.edge_attr_reserved.indexOf(attr);
                    }
                }
                return true;
            };

            /**
             * Retrieves attr list without reserved attributes
             * @param  {String} what 'edges' or 'nodes'
             * @return {Array}      attributes list
             */
            self.clean_attr_list = function (what) {
                if ( -1 != ['edges', 'nodes'].indexOf(what) ) {
                    var attr_list = self.get_attributes(what);

                    if ( null == attr_list ) return;

                    for (var i = attr_list.length - 1; i >= 0; i--) {
                        if ( self.is_attr_reserved(what, attr_list[i]) ) {
                            attr_list = attr_list.splice(i, 1);
                        }
                    }

                }
                return;
            };

            /**
             * Masks nodes/edges selected by the filters
             */
            self.mask = function () {
                if ( undefined == self.filtered ) {
                    self.filtered = {
                        nodes: [],
                        edges: []
                    };
                }

                if ( self.visualized ) {
                    for (var i = self.filters.selection.edges.length - 1; i >= 0; i--) {
                        var edge_id = self.filters.selection.edges[i];

                        if ( 1 == cy.elements('edge[id="' + edge_id + '"]').length ) {
                            // Retrieve from canvas
                            var edge = cy.elements('edge[id="' + edge_id + '"]')[0]._private;
                            // Remove from canvas
                            cy.remove('edge[id="' + edge_id + '"]');
                            // Add to filtered
                            self.filtered.edges.push(edge);
                            // Update self.visualization
                            self.visualization = cy.json().elements;
                        }
                    }

                    for (var i = self.filters.selection.nodes.length - 1; i >= 0; i--) {
                        var node_id = self.filters.selection.nodes[i];

                        if ( 1 == cy.elements('node[id="' + node_id + '"]').length ) {
                            // Retrieve from canvas
                            var node = cy.elements('node[id="' + node_id + '"]')[0]._private;
                            // Remove from canvas
                            cy.remove('node[id="' + node_id + '"]');
                            // Add to filtered
                            self.filtered.nodes.push(node);
                            // Update self.visualization
                            self.visualization = cy.json().elements;
                        }
                    }
                } else {
                    for (var i = self.filters.selection.edges.length - 1; i >= 0; i--) {
                        var edge_id = self.filters.selection.edges[i];

                        for (var j = self.visualization.edges.length - 1; j >= 0; j--) {
                            // Retrieve from self.visualization
                            var edge = self.visualization.edges[j];

                            if ( edge_id == edge.data.id ) {
                                // Add to filtered
                                self.filtered.edges.push(edge);
                                // Remove from self.visualization
                                self.visualization.edges.splice(j, 1);
                            }
                        }
                    }

                    for (var i = self.filters.selection.nodes.length - 1; i >= 0; i--) {
                        var node_id = self.filters.selection.nodes[i];

                        for (var j = self.visualization.nodes.length - 1; j >= 0; j--) {
                            // Retrieve from self.visualization
                            var node = self.visualization.nodes[j];

                            if ( node_id == node.data.id ) {
                                // Add to filtered
                                self.filtered.nodes.push(node);
                                // Remove from self.visualization
                                self.visualization.nodes.splice(j, 1);
                            }
                        }
                    }
                }
                if ( 0 == self.filtered.nodes.length && 0 == self.filtered.edges.length ) {
                    self.filtered = undefined;
                }
                if ( undefined == self.visualization ) {
                    self.filtered = {
                        nodes: [],
                        edges: []
                    }
                } else {
                    if ( undefined == self.nodes ) self.nodes = [];
                    if ( undefined == self.edges ) self.edges = [];
                }

                if ( 'v' == self.filters.token ) {
                    self.filters.load(self.visualization);
                } else if ( 'f' == self.filters.token ) {
                    self.filters.load(self.filtered);
                }
                self.filters.get_attributes();
            };

            /**
             * Unmasks nodes/edges selected by the filters
             */
            self.unmask = function () {
                if ( undefined == self.filtered ) return;
                if ( undefined == self.visualization.nodes ) self.visualization.nodes = [];
                if ( undefined == self.visualization.edges ) self.visualization.edges = [];
                if ( self.visualized ) {
                    for (var i = self.filters.selection.nodes.length - 1; i >= 0; i--) {
                        var node_id = self.filters.selection.nodes[i];

                        for (var j = self.filtered.nodes.length - 1; j >= 0; j--) {
                            var node = self.filtered.nodes[j];

                            if ( node_id == node.data.id ) {
                                // Remove from filtered
                                self.filtered.nodes.splice(j, 1);
                                // Add to visualization
                                self.visualization.nodes.push(node);
                                // Add to canvas
                                cy.add({
                                    group: 'nodes',
                                    data: node.data,
                                    position: node.position
                                });
                            }
                        }
                    }

                    for (var i = self.filters.selection.edges.length - 1; i >= 0; i--) {
                        var edge_id = self.filters.selection.edges[i];

                        for (var j = self.filtered.edges.length - 1; j >= 0; j--) {
                            var edge = self.filtered.edges[j];
                            
                            if ( edge_id == edge.data.id ) {
                                // Check source/target
                                if ( 1 == cy.elements('node[id="' + edge.data.source + '"]').length && 1 == cy.elements('node[id="' + edge.data.target + '"]').length ) {
                                    // Remove from filtered
                                    self.filtered.edges.splice(j, 1);
                                    // Add to visualization
                                    self.visualization.edges.push(edge);
                                    // Add to canvas
                                    cy.add({
                                        group: 'edges',
                                        data: edge.data
                                    });
                                }
                            }
                        }
                    }


                    if ( 0 == self.filtered.nodes.length && 0 == self.filtered.edges.length ) {
                        self.filtered = undefined;
                    }
                } else {
                    for (var i = self.filters.selection.nodes.length - 1; i >= 0; i--) {
                        var node_id = self.filters.selection.nodes[i];

                        for (var j = self.filtered.nodes.length - 1; j >= 0; j--) {
                            var node = self.filtered.nodes[j];

                            if ( node_id == node.data.id ) {
                                // Add to visualization
                                self.visualization.nodes.push(node);
                                // Remove from self.filtered
                                self.filtered.nodes.splice(j, 1);
                            }
                        }
                    }

                    for (var i = self.filters.selection.edges.length - 1; i >= 0; i--) {
                        var edge_id = self.filters.selection.edges[i];

                        for (var j = self.filtered.edges.length - 1; j >= 0; j--) {
                            var edge = self.filtered.edges[j];

                            if ( edge_id == edge.data.id ) {
                                // Check source/target
                                var source_checked = false;
                                var target_checked = false;
                                for (var k = self.visualization.nodes.length - 1; k >= 0; k--) {
                                    var node = self.visualization.nodes[k];
                                    if ( edge.data.target == node.data.id ) {
                                        target_checked = true;
                                    }
                                    if ( edge.data.source == node.data.id ) {
                                        source_checked = true;
                                    }
                                };
                                if ( source_checked && target_checked ) {
                                    // Add to visualization
                                    self.visualization.edges.push(edge);
                                    // Remove from self.filtered
                                    self.filtered.edges.splice(j, 1);
                                }
                            }
                        }
                    }

                    if ( 0 == self.filtered.nodes.length && 0 == self.filtered.edges.length ) {
                        self.filtered = undefined;
                    }
                }

                if ( 'v' == self.filters.token ) {
                    self.filters.load(self.visualization);
                } else if ( 'f' == self.filters.token ) {
                    self.filters.load(self.filtered);
                }
                self.filters.get_attributes();
            };

            /**
             * Navigates the network
             */
            self.navigate = function (session_id, node_id) {
                if ( undefined == self.current ) return;
                if ( undefined == node_id ) {
                    alert('Select a node!');
                    return;
                }
                if ( undefined == self.navi.mode ) {
                    alert('Select a mode!');
                    return;
                }

                var qwait = q.defer();

                http({

                    method: 'POST',
                    data: {
                        action: 'network_navigate',
                        id: session_id,
                        node: node_id,
                        name: self.current_data.name,
                        mode: self.navi.mode
                    },
                    url: 's/'

                }).
                    success(function (data) {
                        self.update(data.neigh);
                        qwait.resolve(data);
                    });

                return qwait.promise;
            };

            self.update = function (toVis) {
                if ( undefined == self.filtered ) {
                    self.filtered = {
                        nodes: [],
                        edges: []
                    };
                }
                if ( undefined == self.visualization ) {
                    self.visualization = {
                        nodes: [],
                        edges: []
                    };
                }
                if ( undefined == self.visualization.nodes ) {
                    self.visualization.nodes = [];
                }
                if ( undefined == self.visualization.edges ) {
                    self.visualization.edges = [];
                }
                
                // Unmask nodes
                var toRM = [];
                for (var i = self.filtered.nodes.length - 1; i >= 0; i--) {
                    var node = self.filtered.nodes[i];

                    if ( -1 != toVis.nodes.indexOf(node.data.id) ) {
                        toRM.push(i);
                        self.visualization.nodes.push(node);
                        if ( self.visualized ) {
                            cy.add({
                                group: 'nodes',
                                data: node.data,
                                position: node.position
                            });
                        }
                    }
                }
                for (var i = 0; i < toRM.length; i++) {
                    self.filtered.nodes.splice(toRM[i], 1);
                }

                // Mask edges
                var toRM = [];
                for (var i = self.visualization.edges.length - 1; i >= 0; i--) {
                    var edge = self.visualization.edges[i];

                    if ( -1 == toVis.edges.indexOf(edge.data.id)) {
                        toRM.push(i)
                        self.filtered.edges.push(edge);
                        if ( self.visualized ) {
                            cy.remove('edge[id="' + edge.data.id + '"]')
                        }
                    }
                }
                for (var i = 0; i < toRM.length; i++) {
                    self.visualization.edges.splice(toRM[i], 1);
                }

                // Mask nodes
                var toRM = [];
                for (var i = self.visualization.nodes.length - 1; i >= 0; i--) {
                    var node = self.visualization.nodes[i];

                    if ( -1 == toVis.nodes.indexOf(node.data.id) ) {
                        toRM.push(i);
                        self.filtered.nodes.push(node);
                        if ( self.visualized ) {
                            cy.remove('node[id="' + node.data.id + '"]');
                        }
                    }
                }
                for (var i = 0; i < toRM.length; i++) {
                    self.visualization.nodes.splice(toRM[i], 1);
                }

                // Unmask edges
                var toRM = [];
                for (var i = self.filtered.edges.length - 1; i >= 0; i--) {
                    var edge = self.filtered.edges[i];

                    if ( -1 != toVis.edges.indexOf(edge.data.id) ) {
                        toRM.push(i);
                        self.visualization.edges.push(edge);
                        if ( self.visualized ) {
                            cy.add({
                                group: 'edges',
                                data: edge.data
                            });
                        }
                    }
                }
                for (var i = 0; i < toRM.length; i++) {
                    self.filtered.edges.splice(toRM[i], 1);
                }

                if ( !self.visualized ) {
                    self.postload();
                }
            };

            // GENERAL
            
            self.reset_ui = function () {
                self.filters.reset_service();
                self.navi.reset_service();
            };

        };

    });

}());
