(function () {
    "use strict";

    define([], function () {

        return function (q, http, rootScope, convertGroup, networkAttribute) {
            var self = this;

            self.list = null;
            self.attributes = networkAttribute;

            /**
             * @param  {String} session_id
             * @return {promise} it contains as .list the network list
             */
            self.get_list = function (session_id) {
                var qwait = q.defer();

                http({

                    method: 'POST',
                    data: {
                        action: 'get_network_list',
                        id: session_id
                    },
                    url: 's/'

                }).
                    success(function(data) {
                        qwait.resolve(data);
                    });

                return qwait.promise;
            };

            /**
             * Reloads the network list with the one of the given id
             * @param  {string} session_id
             */
            self.reload_list = function (session_id) {
                self.get_list(session_id).then(function (data) {
                    if (0 != data['err'] ) {
                        document.location.hash = '#/';
                    } else {
                        if ( 0 == data.list.length ) {
                            self.list = null;
                        } else {
                            self.list = data.list;
                            rootScope.$broadcast('trigger_apply_sif');
                        }
                    }
                });
            }

            /**
             * Loads a network, after converting it, if required
             * @param {int} id network id
             * @param {String} session_id
             */
            self.convert = function (network, session_id) {
                if ( 0 == network.status ) {
                    var qwait = q.defer();

                    http({

                        method: 'POST',
                        data: {
                            action: 'convert_network',
                            id: session_id,
                            network_id: network.id
                        },
                        url: 's/'

                    }).
                        success(function (data) {
                            if ( undefined != data['status'] ) {
                                self.list[network.id].status = parseInt(data['status']);
                                self.list[network.id].data = data['data'];
                            }
                            qwait.resolve(data);
                        });

                    return qwait.promise;
                }
            };

            /**
             * @param  {Object}  network from self.list
             * @return {Boolean}         If the given network is converted
             */
            self.isConverted = function (network) {
                return 1 == network.status;
            };

            /**
             * @param  {Object}  network from self.list
             * @return {Boolean}         If the given network is to convert
             */
            self.isToConvert = function (network) {
                return 0 == network.status;
            }

            /**
             * Applies SIF information to the network list
             * @param  {Object} sif
             * @param  {String} sample_col
             */
            self.apply_sif = function (info) {
                // Retrieve SIF
                var sif = info['sif'];

                if ( undefined != sif) {

                    // SIF columns
                    var cols = info['sif_keys'];
                    // SIF sample column
                    var sample_col = info['sif_sample_col'];

                    if ( null != self.list) {
                        // Apply to each network in self.list
                        for (var i = self.list.length - 1; i >= 0; i--) {
                            var network = self.list[i];

                            // Look for the network in the SIF
                            var j = sif[sample_col].indexOf(network.name);

                            // Start building network-specific SIF_data
                            var sif_data = {}
                            if ( -1 != j ) {
                                // Retrieve SIF data
                                for (var k = cols.length - 1; k >= 0; k--) {
                                    var col = cols[k];
                                    sif_data[col] = sif[col][j];
                                };
                            }
                            // Assign SIF data
                            self.list[i]['sif_data'] = sif_data;

                            // Assign single-sample status
                            if ( 0 == Object.keys(sif_data).length ) {
                                // multi-sample network
                                self.list[i]['single'] = false;
                            } else {
                                // single-sample network
                                self.list[i]['single'] = true;
                            }
                        };
                    }
                }
            };

            /**
             * Trigger network inspection
             * @param  {integer} network_id
             */
            self.inspect = function (network_id) {
                rootScope.$broadcast('inspect_network', self.list[network_id]);
            };

            /**
             * Rename network
             * @param  {string} session_id
             * @param  {string} old_name
             */
            self.rename = function (session_id, network_id) {
                var new_name = prompt('To rename the network insert the new name:');

                // Check if new_name is already in use
                var checked = true;
                for (var i = self.list.length - 1; i >= 0; i--) {
                    if ( new_name == self.list[i].name || null == new_name || '' == new_name ) {
                        checked = false;
                    }
                }

                if ( !checked ) {
                    alert('Name already in use.');
                } else {
                    // Rename
                    
                    http({

                        method: 'POST',
                        data: {
                            action: 'rename_network',
                            id: session_id,
                            network_id: network_id,
                            name: new_name
                        },
                        url: 's/'

                    }).
                        success(function (data) {
                            self.reload_list(session_id);
                        });
                }
            };

            /**
             * Provides links to download a network
             * @param  {string} session_id
             * @param  {string} old_name
             */
            self.download = function (session_id, network_id) {
                var ans = prompt('1: GraphML format\n2: JSON format');

                if ( 1 == parseInt(ans) ) {
                    window.open('s/' + session_id + '/' + self.list[network_id].name + '.graphml', '_blank');
                } else if ( 2 == parseInt(ans) ) {
                    window.open('s/' + session_id + '/' + self.list[network_id].name + '.json', '_blank');
                } else {
                    alert('Non-existent option.');
                }
            };

            /**
             * Removes a network
             * @param  {string} session_id
             * @param  {string} old_name
             */
            self.remove = function (session_id, network_id) {
                var ans = prompt('Do you really want to remove network "' + self.list[network_id].name + '"? (y/n)');

                if ( -1 == ['y','n'].indexOf(ans) ) {
                    alert('Non-existent option.');
                } else if ( 'y' == ans ) {
                    // Remove
                    
                    http({

                        method: 'POST',
                        data: {
                            action: 'remove_network',
                            id: session_id,
                            network_id: network_id
                        },
                        url: 's/'

                    }).
                        success(function (data) {
                            self.reload_list(session_id);
                        });
                }
            };

            /**
             * Overwrites current visualization on the given network
             * @param  {string} session_id
             * @param  {string} network_id
             */
            self.overwrite = function (session_id, network_id) {
                var qwait = q.defer();
                var network;

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
                        name: self.list[network_id].name
                    },
                    url: 's/'

                }).
                    success(function (data) {
                        if ( 0 == data['err'] ) {
                            alert('Overwritten.');
                        }
                        self.reload_list(session_id);
                        qwait.resolve(data);
                    });

                return q.promise;
            };

            /**
             * Returns 
             * @param  {String} network_name
             * @return {Object]}      network
             */
            self.get_network_from_name = function (network_name) {
                for (var i = self.list.length - 1; i >= 0; i--) {
                    var network = self.list[i];
                    if ( network_name == network.name ) return(network);
                }
            };

            // GROUP CONVERSION

            self.conversion = convertGroup;
            self.toggle_conversion_panel = function (session_id) {
                self.conversion.list = [];
                for (var i = self.list.length - 1; i >= 0; i--) {
                    var net = self.list[i];
                    if ( self.isToConvert(net) ) {
                        self.conversion.list.push(net);
                    }
                }
                self.conversion.toggle(session_id);
            }
            
            /**
             * Begins the conversion of multiple networks
             */
            self.start_converting_group = function () {
                var toConvert = [];

                var ks = Object.keys(self.conversion.group.networks);
                for (var i = ks.length - 1; i >= 0; i--) {
                    var k = ks[i];
                    if ( self.conversion.group.networks[k] ) {
                        for (var j = self.list.length - 1; j >= 0; j--) {
                            var network = self.list[j];
                            if ( k == network.name ) {
                                toConvert.push(network);
                            }
                        }
                    }
                }

                self.convert_group(toConvert);
            };

            /**
             * Iteratively converts a list of networks
             * @param  {array} networks array of networks to convert
             */
            self.convert_group = function (networks) {
                var net = networks.pop();
                self.convert(net, self.conversion.group.id).then(function (data) {
                    if ( 0 == networks.length ) {
                        self.conversion.toggle(networks);
                    } else {
                        self.convert_group(networks);
                    }
                });
            };

            // GENERAL
            
            self.reset_ui = function () {
                self.conversion.reset_service();
                self.attributes.reset_service();
            };

        };

    });

}());
