(function () {
    "use strict";

    define([], function () {

        return function () {
            var self = this;

            self.network = undefined;

            self.attributes = {
                nodes: {
                    labels: [],
                    values: {}
                },
                edges: {
                    labels: [],
                    values: {}
                }
            };

            self.selection = {
                nodes: [],
                edges: []
            };

            /**
             * Initializes the filter service
             * @param  {Object} network {nodes:[...], edges:[...]}
             */
            self.init = function (network, token) {
                if ( undefined == network ) return;
                self.reset_selection();
                self.reset_list();

                self.set_token(token);
                self.load(network);
                self.get_attributes();
            };

            /**
             * Loads a network in the service
             * @param  {Object} network {nodes:[...], edges:[...]}
             */
            self.load = function (network) {
                if ( undefined == network ) {
                    self.reset_service();
                    return;
                }
                self.network = network;
                self.reset_attributes();
            };

            /**
             * Sets service token, identifies input for template changes
             * @param {String} token
             */
            self.set_token = function (token) {
                self.token = token;
            };

            /**
             * Resets self.attribtues
             */
            self.reset_attributes = function () {
                self.attributes = {
                    nodes: {
                        labels: [],
                        values: {}
                    },
                    edges: {
                        labels: [],
                        values: {}
                    }
                };
            };

            /**
             * Resets self.network
             */
            self.reset_network = function () {
                self.network = undefined;
            };

            /**
             * Resets self.selection
             */
            self.reset_selection = function () {
                self.selection = {
                    nodes: [],
                    edges: []
                };
            };

            /**
             * Resets self.token
             */
            self.reset_token = function () {
                self.token = undefined;
            };

            /**
             * @return {Boolean} If a network is loaded in the service
             */
            self.isLoaded = function () {
                return undefined != self.network;
            };

            /**
             * @param  {String}  token
             */
            self.is_token = function (token) {
                return token == self.token;
            };

            /**
             * Retrieves names and values of node/edge attributes
             * for the loaded network
             */
            self.get_attributes = function () {
                if ( undefined == self.network ) return;
                
                // Get node attributes
                for (var i = self.network.nodes.length - 1; i >= 0; i--) {
                    var node = self.network.nodes[i].data;

                    var node_attrs = Object.keys(node);
                    for (var k = node_attrs.length - 1; k >= 0; k--) {
                        var node_attr = node_attrs[k];

                        if ( -1 == self.attributes.nodes.labels.indexOf(node_attr) ) {
                            self.attributes.nodes.labels.push(node_attr);
                        }

                        if ( -1 == Object.keys(self.attributes.nodes.values).indexOf(node_attr) ) {
                            self.attributes.nodes.values[node_attr] = [node[node_attr]];
                        } else {
                            if ( -1 == self.attributes.nodes.values[node_attr].indexOf(node[node_attr]) ) {
                                self.attributes.nodes.values[node_attr].push(node[node_attr]);
                            }
                        }
                    }
                }
                // Get edge attributes
                for (var i = self.network.edges.length - 1; i >= 0; i--) {
                    var edge = self.network.edges[i].data;

                    var edge_attrs = Object.keys(edge);
                    for (var k = edge_attrs.length - 1; k >= 0; k--) {
                        var edge_attr = edge_attrs[k];

                        if ( -1 == self.attributes.edges.labels.indexOf(edge_attr) ) {
                            self.attributes.edges.labels.push(edge_attr);
                        }

                        if ( -1 == Object.keys(self.attributes.edges.values).indexOf(edge_attr) ) {
                            self.attributes.edges.values[edge_attr] = [edge[edge_attr]];
                        } else {
                            if ( -1 == self.attributes.edges.values[edge_attr].indexOf(edge[edge_attr]) ) {
                                self.attributes.edges.values[edge_attr].push(edge[edge_attr]);
                            }
                        }

                    }
                }
            };

            //--------//
            // FILTER //
            //--------//
            
            self.list = {
                nodes: [],
                edges: []
            };

            /**
             * Adds a filter to the service
             * @param {String} what 'nodes' or 'edges'
             */
            self.add = function (what) {
                if ( -1 != ['nodes','edges'].indexOf(what) ) {
                    self.list[what].push({
                        combine: '',
                        attribute: '',
                        condition: '',
                        value: '',
                        object: false
                    });
                }
            };

            /**
             * @return {Boolean} If the service has any filter at all
             */
            self.has = function () {
                return 0 != self.list.nodes.length || 0 != self.list.edges.length;
            };

            /**
             * @return {Boolean} If the current filters are complete
             */
            self.check = function () {
                var checked = true
                for (var i = self.list.nodes.length - 1; i >= 0; i--) {
                    var filter = self.list.nodes[i];
                    if ( '' == filter.combine && 0 != i ) checked = false;
                    if ( '' == filter.attribute ) checked = false;
                    if ( '' == filter.condition ) checked = false;
                    if ( '' == filter.value ) checked = false;
                }
                for (var i = self.list.edges.length - 1; i >= 0; i--) {
                    var filter = self.list.edges[i];
                    if ( '' == filter.combine && 0 != i ) checked = false;
                    if ( '' == filter.attribute ) checked = false;
                    if ( '' == filter.condition ) checked = false;
                    if ( '' == filter.value ) checked = false;
                }
                return checked;
            };

            /**
             * Removes the index-th filter
             * @param {String} what 'nodes' or 'edges'
             * @param  {integer} index filter index, if no index is provided removes the last filter
             */
            self.remove = function (what, index) {
                if ( -1 == ['nodes', 'edges'].indexOf(what) ) return;
                if ( undefined == index || index > self.list[what].length || index < 0) {
                    index = self.list[what].length - 1;
                }
                self.list[what].splice(index, 1);
            };

            /**
             * Selects based on the filters
             */
            self.apply = function () {
                if ( self.check() ) {
                    self.reset_selection();

                    // NODES
                    for (var i = self.network.nodes.length - 1; i >= 0; i--) {
                        var node = self.network.nodes[i].data;
                        
                        var res = false;
                        for (var j = 0; j < self.list.nodes.length; j++) {
                            var filter = self.list.nodes[j];

                            var k = node[filter.attribute];
                            var v = filter.value;
                            var tmpRes = false;

                            if ( 'e' == filter.condition ) {
                                tmpRes = ( '' + k == '' + v );
                            } else if ( 'ne' == filter.condition ) {
                                tmpRes = ( '' + k != '' + v );
                            } else if ( 'lt' == filter.condition ) {
                                tmpRes = ( parseFloat(k) < parseFloat(v) );
                            } else if ( 'le' == filter.condition ) {
                                tmpRes = ( parseFloat(k) <= parseFloat(v) );
                            } else if ( 'gt' == filter.condition ) {
                                tmpRes = ( parseFloat(k) > parseFloat(v) );
                            } else if ( 'ge' == filter.condition ) {
                                tmpRes = ( parseFloat(k) >= parseFloat(v) );
                            } else if ( 'c' == filter.condition ) {
                                tmpRes = ( -1 != k.indexOf(v) );
                            }
                            
                            if ( '' == filter.combine ) {
                                res = tmpRes;
                            } else if ( 'AND' == filter.combine ) {
                                res = res && tmpRes;
                            } else if ( 'OR' == filter.combine ) {
                                res = res || tmpRes;
                            }
                        }

                        if ( res ) {
                            self.selection.nodes.push(node.id);
                        }
                    }

                    // EDGES
                    for (var i = self.network.edges.length - 1; i >= 0; i--) {
                        var edge = self.network.edges[i].data;

                        var res = false;
                        for (var j = 0; j < self.list.edges.length; j++) {
                            var filter = self.list.edges[j];

                            var k = edge[filter.attribute];
                            var v = filter.value;
                            var tmpRes = false;

                            if ( 'e' == filter.condition ) {
                                tmpRes = ( '' + k == '' + v );
                            } else if ( 'ne' == filter.condition ) {
                                tmpRes = ( '' + k != '' + v );
                            } else if ( 'lt' == filter.condition ) {
                                tmpRes = ( parseFloat(k) < parseFloat(v) );
                            } else if ( 'le' == filter.condition ) {
                                tmpRes = ( parseFloat(k) <= parseFloat(v) );
                            } else if ( 'gt' == filter.condition ) {
                                tmpRes = ( parseFloat(k) > parseFloat(v) );
                            } else if ( 'ge' == filter.condition ) {
                                tmpRes = ( parseFloat(k) >= parseFloat(v) );
                            } else if ( 'c' == filter.condition ) {
                                tmpRes = ( -1 != k.indexOf(v) );
                            }

                            if ( '' == filter.combine ) {
                                res = tmpRes;
                            } else if ( 'AND' == filter.combine ) {
                                res = res && tmpRes;
                            } else if ( 'OR' == filter.combine ) {
                                res = res || tmpRes;
                            }

                        }

                        // If source or target is selected, select this too
                        if ( -1 != self.selection.nodes.indexOf(edge.source) ) res = true;
                        if ( -1 != self.selection.nodes.indexOf(edge.target) ) res = true;

                        if ( res ) {
                            self.selection.edges.push(edge.id);
                        }
                    }
                }
            };

            /**
             * Resets self.list
             */
            self.reset_list = function () {
                self.list = {
                    nodes: [],
                    edges: []
                };
            };

            // GENERAL
            
            self.reset_service = function () {
                self.reset_list();
                self.reset_attributes();
                self.reset_network();
                self.reset_token();
                self.reset_selection();
            }

        };

    });

}());
