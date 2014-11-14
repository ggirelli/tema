(function () {
    "use strict";

    define([], function () {

        return function (q, http, rootScope, networkGroup) {
            var self = this;

            self.list = null;

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

                // Save
                
                http({

                    method: 'POST',
                    data: {
                        action: 'save_network',
                        id: session_id,
                        network: JSON.stringify(cy.json().elements),
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

            // GROUP CONVERSION

            self.conversion = networkGroup;
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

            // ATTRIBUTES
            
            self.attributes = {
                label: null,
                options: null,
                edges: {},
                nodes: {}
            };

            /**
             * @return {Boolean} if the action to perform on the attributes has been decided
             */
            self.is_attr_doing = function (label) {
                if ( undefined == label ) return null != self.attributes.label;
                return label == self.attributes.label;
            };

            /**
             * Triggers opening of attribute operations specific panels
             * @param  {string} label attribute operation name
             */
            self.do_attr = function (label) {
                self.attributes.label = label;
                if ( null == label ) {
                    self.attributes = {
                        label: null,
                        options: null,
                        edges: {},
                        nodes: {}
                    };
                } else {
                    self.attributes.options = {
                        errMsg: []
                    };
                }
            };

            /**
             * Checks the self.attributes structure for different attribute operations
             * Registers form errors in self.attributes.options.errMsh
             * @return {Boolean}
             */
            self.check_attr = function () {
                self.attributes.options.errMsg = [];

                if ( 'add_new' == self.attributes.label ) {

                    if ( 'manual' == self.attributes.options.input ) {

                        // Check attr_type
                        if ( undefined == self.attributes.options.type || null == self.attributes.options.type ) {
                            self.attributes.options.errMsg.push('Please, select a type of attribute.');
                            return;
                        }

                        // Check attr_name
                        var checked = true;
                        var attr_list = Object.keys(cy.json().elements[self.attributes.options.type][0].data);
                        for (var i = attr_list.length - 1; i >= 0; i--) {
                            if ( self.attributes.options.name == attr_list[i] ) {
                                checked = false;
                            }
                        }

                        if ( !checked ) {
                            self.attributes.options.errMsg.push('Name already in use.');
                        }
                        if ( null == self.attributes.options.name || '' == self.attributes.options.name ) {
                            checked = false;
                            self.attributes.options.errMsg.push('Please, provide a name.');
                        }

                        // Check csv
                        if ( checked ) {
                            if ( undefined != self.attributes.options.values ) {
                                var n_el = cy.json().elements[self.attributes.options.type].length;
                                var n_val = self.attributes.options.values.split(',').length;
                                if ( n_el !=  n_val ) {
                                    self.attributes.options.errMsg.push('Found ' + n_val + ' values for ' + n_el + ' ' + self.attributes.options.type + '.');
                                }
                            } else {
                                self.attributes.options.errMsg.push('Please, provide attribute values.');
                            }
                        }

                    } else if ( 'index' == self.attributes.options.input ) {

                        // Check attr_name
                        var checked = true;
                        var attr_list = Object.keys(cy.json().elements.nodes[0].data);
                        for (var i = attr_list.length - 1; i >= 0; i--) {
                            if ( self.attributes.options.name == attr_list[i] ) {
                                checked = false;
                            }
                        }

                        if ( !checked ) {
                            self.attributes.options.errMsg.push('Name already in use.');
                        }
                        if ( null == self.attributes.options.name || '' == self.attributes.options.name ) {
                            checked = false;
                            self.attributes.options.errMsg.push('Please, provide a name.');
                        }

                        // Check index
                        if ( checked ) {
                            if ( undefined != self.attributes.options.index ) {
                                return true;
                            } else {
                                self.attributes.options.errMsg.push('Please, select an index.');
                            }
                        }

                    } else {
                        self.attributes.options.errMsg.push('Please, select an input manner.');
                    }
                } else if ( 'combine' == self.attributes.label ) {
                    // Check attr_type
                    if ( undefined == self.attributes.options.type || null == self.attributes.options.type ) {
                        self.attributes.options.errMsg.push('Please, select a type of attribute.');
                        return;
                    }

                    // Check attr_name
                    var checked = true;
                    var attr_list = Object.keys(cy.json().elements[self.attributes.options.type][0].data);
                    for (var i = attr_list.length - 1; i >= 0; i--) {
                        if ( self.attributes.options.name == attr_list[i] ) {
                            checked = false;
                        }
                    }

                    if ( !checked ) {
                        self.attributes.options.errMsg.push('Name already in use.');
                    }
                    if ( null == self.attributes.options.name || '' == self.attributes.options.name ) {
                        checked = false;
                        self.attributes.options.errMsg.push('Please, provide a name.');
                    }

                    // Check selection
                    if ( checked ) {
                        if ( 2 > self.attr_get_selected().length ) {
                            checked = false;
                            self.attributes.options.errMsg.push('Please, select at least 2 attributes.');
                        }
                    }

                    // Check function
                    if ( checked ) {
                        if ( undefined == self.attributes.options.function && '' == self.attributes.options.function ) {
                            self.attributes.options.errMsg.push('Please, provide a function.');
                            checked = false;
                        }
                    }

                    return checked;
                }

                return 0 == self.attributes.options.errMsg;
            };

            /**
             * @return {Boolean} If any form error has been registered.
             */
            self.is_attr_ok = function () {
                if ( null == self.attributes.options ) return true;
                return undefined == self.attributes.options.errMsg;
            };

            /**
             * Performs some attribute operations
             * @param  {String} session_id
             * @param  {String} label      attribute operation name
             * @return {promise}
             */
            self.attr_apply = function (session_id, label) {
                if ( self.check_attr() ) {
                    if ( 'add_new' == label ) {
                        var qwait = q.defer();

                        if ( 'manual' == self.attributes.options.input ) {
                            http({

                                method: 'POST',
                                data: {
                                    action: 'add_attr',
                                    id: session_id,
                                    name: 'json_tmp_net',
                                    network: JSON.stringify(cy.json().elements),
                                    attr_type: self.attributes.options.type,
                                    attr_name: self.attributes.options.name,
                                    attr_val: self.attributes.options.values
                                },
                                url: 's/'

                            }).
                                success(function (data) {
                                    if ( 0 == data['err'] ) {
                                        cy.load(data['net']);
                                        self.do_attr(null);
                                    }
                                    qwait.resolve(data);
                                });
                        } else if ( 'index' == self.attributes.options.input ) {
                            http({

                                method: 'POST',
                                data: {
                                    action: 'add_attr_index',
                                    id: session_id,
                                    name: 'json_tmp_net',
                                    network: JSON.stringify(cy.json().elements),
                                    attr_name: self.attributes.options.name,
                                    attr_index: self.attributes.options.index
                                },
                                url: 's/'

                            }).
                                success(function (data) {
                                    if ( 0 == data['err'] ) {
                                        cy.load(data['net']);
                                        self.do_attr(null);
                                    }
                                    qwait.resolve(data);
                                });
                        }

                        return qwait.promise;
                    } else if ( 'combine' == label ) {
                        var qwait = q.defer();

                        http({

                            method: 'POST',
                            data: {
                                action: 'combine_attr',
                                id: session_id,
                                name: 'json_tmp_net',
                                network: JSON.stringify(cy.json().elements),
                                attr_type: self.attributes.options.type,
                                attr_name: self.attributes.options.name,
                                attr_list: self.attr_get_selected().toString(),
                                attr_function: self.attributes.options.function
                            },
                            url: 's/'

                        }).
                            success(function (data) {
                                if ( 0 == data['err'] ) {
                                    if ( undefined != data.net[self.attributes.options.type][0].data[self.attributes.options.name] ) {
                                        cy.load(data.net);
                                        self.do_attr(null);
                                    } else {
                                        self.attributes.options.errMsg = ['Something went wrong with the provided function.'];
                                    }
                                }
                                qwait.resolve(data);
                            });

                        return qwait.promise;
                    }
                }
            };

            /**
             * @return {Boolean} If attributes were selected from the list
             */
            self.attr_selected = function () {
                if ( null != self.attributes.options ) {
                    if ( undefined != self.attributes.options.type ) {
                        var ks = Object.keys(self.attributes[self.attributes.options.type]);
                        for (var i = ks.length - 1; i >= 0; i--) {
                            var k = ks[i];
                            if ( self.attributes[self.attributes.options.type][k] ) {
                                return true;
                            }
                        }
                    }
                }
                return false;
            };

            /**
             * @return {Array} list of selected attributes
             */
            self.attr_get_selected = function () {
                if ( null != self.attributes.options ) {
                    if ( undefined != self.attributes.options.type ) {
                        var list = [];
                        var ks = Object.keys(self.attributes[self.attributes.options.type]);
                        for (var i = ks.length - 1; i >= 0; i--) {
                            var k = ks[i];
                            if ( self.attributes[self.attributes.options.type][k] ) {
                                list.push(k);
                            }
                        }
                        return list;
                    }
                }
                return [];
            };

            /**
             * Renames attribute, new name is asked through prompt
             * @param  {String} session_id
             * @param  {String} old_name   old attribute name
             * @param  {String} group      'nodes'/'edges'
             * @return {promise}
             */
            self.attr_rename = function (session_id, old_name, group) {
                var new_name = prompt('Insert the new name:');
                var checked = true;
                var attr_list = Object.keys(cy.json().elements[group][0].data);
                for (var i = attr_list.length - 1; i >= 0; i--) {
                    if (new_name == attr_list[i] ) {
                        checked = false;
                    }
                }

                if ( !checked ) {
                    alert('Name already in use.');
                } else if ( null == new_name || '' == new_name ) {
                    alert('Please, provide a name.');
                } else {
                    var qwait = q.defer();

                    http({

                        method: 'POST',
                        data: {
                            action: 'rename_attr',
                            id: session_id,
                            name: 'json_tmp_net',
                            network: JSON.stringify(cy.json().elements),
                            attr_type: group,
                            attr_name: old_name,
                            attr_new_name: new_name
                        },
                        url: 's/'

                    }).
                        success(function (data) {
                            if ( 0 == data.err ) {
                                cy.load(data.net);
                                self.do_attr(null);
                            }
                            qwait.resolve(data);
                        });

                    return q.promise;
                }
            };

            /**
             * Removes attribute
             * @param  {String} session_id
             * @param  {String} old_name   old attribute name
             * @param  {String} group      'nodes'/'edges'
             * @return {promise}
             */
            self.attr_remove = function (session_id, old_name, group) {
                var ans = prompt('Do you really want to remove the ' + group + ' attribute "' + old_name + '"? (y/n)');

                if ( null == ans || '' == ans || -1 == ['y', 'n'].indexOf(ans) ) {
                    alert('Non-existent option.');
                } else if ( 'y' == ans ) {
                    var qwait = q.defer();

                    http({

                        method: 'POST',
                        data: {
                            action: 'remove_attr',
                            id: session_id,
                            name: 'json_tmp_net',
                            network: JSON.stringify(cy.json().elements),
                            attr_type: group,
                            attr_name: old_name,
                        },
                        url: 's/'

                    }).
                        success(function (data) {
                            if ( 0 == data.err ) {
                                cy.load(data.net);
                                self.do_attr(null);
                            }
                            qwait.resolve(data);
                        });

                    return q.promise;
                }
            };            

        };

    });

}());
