(function () {
    "use strict";

    define([], function () {

        return function (q, http, rootScope) {
            var self = this;

            self.current_network = {};

            /**
             * Default network
             * @type {Object}
             */
            self.elements = {
                nodes: [
                    { data: { id: "n1", name: "Welcome" } },
                    { data: { id: "n2", name: "to" } },
                    { data: { id: "n3", name: "SOGI" } }
                ],
                edges: [
                    { data: { id: "e1", source: "n1", target: "n2" } },
                    { data: { id: "e2", source: "n2", target: "n3" } },
                    { data: { id: "e3", source: "n3", target: "n2" } }
                ]
            };

            /**
             * Initialize cytoscape and load the default network
             */
            self.init = function () {
                // Initialize canvas
                window.cy = cytoscape({
                    container: document.getElementById('canvas'),
                    maxZoom: 5,
                    hideEdgesOnViewport: true,
                    hideLabelsOnViewport: true,
                    textureOnViewport: true,
                    layout: {
                        name: 'grid',
                        fit: true,
                        padding: 5
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
                        });

                        cy.on('tap', 'edge', function(e){
                            var edge = e.cyTarget;

                            cy.elements().addClass('faded');
                            edge.source().removeClass('faded');
                            edge.target().removeClass('faded');

                            // Broadcast to inspector
                        });

                        cy.on('tap', function(e){
                            if( e.cyTarget === cy ){
                                // Broadcast to inspector
                                cy.elements().removeClass('faded');
                            }
                        });

                        cy.load(self.elements, undefined);
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
                            self.current_network = network;
                            cy.load(data.network);
                        } else {
                            console.log(data);
                        }
                        qwait.resolve(data);
                    });

                return qwait.promise;
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
                return self.current_network.name == network_name;
            };

            /**
             * Saves current visualization on the server
             * @param  {string} session_id
             * @param  {Object} networks   network list
             */
            self.save = function (session_id, networks) {
                var new_name = prompt('Insert the name for the new network:');

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
                        
                        http({

                            method: 'POST',
                            data: {
                                action: 'save_network',
                                id: session_id,
                                network: JSON.stringify(cy.json().elements),
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
                    if ( -1 != Object.keys(cy.json().elements).indexOf(what) ) {
                        return Object.keys(cy.json().elements[what][0].data);
                    }
                }
                return null;
            }

        };

    });

}());
