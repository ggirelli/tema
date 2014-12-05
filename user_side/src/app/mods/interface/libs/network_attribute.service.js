(function () {
    "use strict";

    define([], function () {

        return function (q, http, rootScope) {
            var self = this;
            
            self.list = {
                label: null,
                options: null,
                edges: {},
                nodes: {}
            };

            /**
             * @return {Boolean} if the action to perform on the attributes has been decided
             */
            self.is_attr_doing = function (label) {
                if ( undefined == label ) return null != self.list.label;
                return label == self.list.label;
            };

            /**
             * Triggers opening of attribute operations specific panels
             * @param  {string} label attribute operation name
             */
            self.do_attr = function (label) {
                self.list.label = label;
                if ( null == label ) {
                    self.list = {
                        label: null,
                        options: null,
                        edges: {},
                        nodes: {}
                    };
                } else {
                    self.list.options = {
                        errMsg: []
                    };
                }
            };

            /**
             * Checks the self.list structure for different attribute operations
             * Registers form errors in self.list.options.errMsh
             * @return {Boolean}
             */
            self.check_attr = function () {
                self.list.options.errMsg = [];

                if ( 'add_new' == self.list.label ) {

                    if ( 'manual' == self.list.options.input ) {

                        // Check attr_type
                        if ( undefined == self.list.options.type || null == self.list.options.type ) {
                            self.list.options.errMsg.push('Please, select a type of attribute.');
                            return;
                        }

                        // Check attr_name
                        var checked = true;
                        var attr_list = Object.keys(cy.json().elements[self.list.options.type][0].data);
                        for (var i = attr_list.length - 1; i >= 0; i--) {
                            if ( self.list.options.name == attr_list[i] ) {
                                checked = false;
                            }
                        }

                        if ( !checked ) {
                            self.list.options.errMsg.push('Name already in use.');
                        }
                        if ( null == self.list.options.name || '' == self.list.options.name ) {
                            checked = false;
                            self.list.options.errMsg.push('Please, provide a name.');
                        }

                        // Check csv
                        if ( checked ) {
                            if ( undefined != self.list.options.values ) {
                                var n_el = cy.json().elements[self.list.options.type].length;
                                var n_val = self.list.options.values.split(',').length;
                                if ( n_el !=  n_val ) {
                                    self.list.options.errMsg.push('Found ' + n_val + ' values for ' + n_el + ' ' + self.list.options.type + '.');
                                }
                            } else {
                                self.list.options.errMsg.push('Please, provide attribute values.');
                            }
                        }

                    } else if ( 'index' == self.list.options.input ) {

                        // Check attr_name
                        var checked = true;
                        var attr_list = Object.keys(cy.json().elements.nodes[0].data);
                        for (var i = attr_list.length - 1; i >= 0; i--) {
                            if ( self.list.options.name == attr_list[i] ) {
                                checked = false;
                            }
                        }

                        if ( !checked ) {
                            self.list.options.errMsg.push('Name already in use.');
                        }
                        if ( null == self.list.options.name || '' == self.list.options.name ) {
                            checked = false;
                            self.list.options.errMsg.push('Please, provide a name.');
                        }

                        // Check index
                        if ( checked ) {
                            if ( undefined != self.list.options.index ) {
                                return true;
                            } else {
                                self.list.options.errMsg.push('Please, select an index.');
                            }
                        }

                    } else if ( 'ginfo' == self.list.options.input ) {
                        // Check selection of HUGO-containing attr
                        if ( checked ) {
                            if ( undefined == self.list.options.hugo ) {
                                self.list.options.errMsg.push('Please, select the attribute containing the HUGO names.')
                            }
                        }
                    } else {
                        self.list.options.errMsg.push('Please, select an input manner.');
                    }
                } else if ( 'combine' == self.list.label ) {
                    // Check attr_type
                    if ( undefined == self.list.options.type || null == self.list.options.type ) {
                        self.list.options.errMsg.push('Please, select a type of attribute.');
                        return;
                    }

                    // Check attr_name
                    var checked = true;
                    var attr_list = Object.keys(cy.json().elements[self.list.options.type][0].data);
                    for (var i = attr_list.length - 1; i >= 0; i--) {
                        if ( self.list.options.name == attr_list[i] ) {
                            checked = false;
                        }
                    }

                    if ( !checked ) {
                        self.list.options.errMsg.push('Name already in use.');
                    }
                    if ( null == self.list.options.name || '' == self.list.options.name ) {
                        checked = false;
                        self.list.options.errMsg.push('Please, provide a name.');
                    }

                    // Check selection
                    if ( checked ) {
                        if ( 2 > self.attr_get_selected().length ) {
                            checked = false;
                            self.list.options.errMsg.push('Please, select at least 2 attributes.');
                        }
                    }

                    // Check function
                    if ( checked ) {
                        if ( undefined == self.list.options.function && '' == self.list.options.function ) {
                            self.list.options.errMsg.push('Please, provide a function.');
                            checked = false;
                        }
                    }

                    return checked;
                }

                return 0 == self.list.options.errMsg;
            };

            /**
             * @return {Boolean} If any form error has been registered.
             */
            self.is_attr_ok = function () {
                if ( null == self.list.options ) return true;
                return undefined == self.list.options.errMsg;
            };

            /**
             * Performs some attribute operations
             * @param  {String} session_id
             * @param  {String} label      attribute operation name
             * @return {promise}
             */
            self.attr_apply = function (network, session_id, label) {
                if ( self.check_attr() ) {
                    if ( 'add_new' == label ) {
                        var qwait = q.defer();

                        if ( 'manual' == self.list.options.input ) {
                            http({

                                method: 'POST',
                                data: {
                                    action: 'add_attr',
                                    id: session_id,
                                    name: 'json_tmp_net',
                                    network: JSON.stringify(network),
                                    attr_type: self.list.options.type,
                                    attr_name: self.list.options.name,
                                    attr_val: self.list.options.values
                                },
                                url: 's/'

                            }).
                                success(function (data) {
                                    if ( 0 == data['err'] ) {
                                        rootScope.$broadcast('load_in_canvas', data.net);
                                        self.do_attr(null);
                                    }
                                    qwait.resolve(data);
                                });
                        } else if ( 'index' == self.list.options.input ) {
                            http({

                                method: 'POST',
                                data: {
                                    action: 'add_attr_index',
                                    id: session_id,
                                    name: 'json_tmp_net',
                                    network: JSON.stringify(network),
                                    attr_name: self.list.options.name,
                                    attr_index: self.list.options.index
                                },
                                url: 's/'

                            }).
                                success(function (data) {
                                    if ( 0 == data['err'] ) {
                                        rootScope.$broadcast('load_in_canvas', data.net);
                                        self.do_attr(null);
                                    }
                                    qwait.resolve(data);
                                });
                        } else if ( 'ginfo' == self.list.options.input ) {
                            http({

                                method: 'POST',
                                data: {
                                    action: 'add_ginfo_attrs',
                                    id: session_id,
                                    name: 'json_tmp_net',
                                    network: JSON.stringify(network),
                                    go_type: 'default',
                                    attr_hugo: self.list.options.hugo
                                },
                                url: 's/'

                            }).
                                success(function (data) {
                                    //console.log(data)
                                    if ( 0 == data['err'] ) {
                                        rootScope.$broadcast('load_in_canvas', data.net);
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
                                network: JSON.stringify(network),
                                attr_type: self.list.options.type,
                                attr_name: self.list.options.name,
                                attr_list: self.attr_get_selected().toString(),
                                attr_function: self.list.options.function
                            },
                            url: 's/'

                        }).
                            success(function (data) {
                                if ( 0 == data['err'] ) {
                                    if ( undefined != data.net[self.list.options.type][0].data[self.list.options.name] ) {
                                        rootScope.$broadcast('load_in_canvas', data.net);
                                        self.do_attr(null);
                                    } else {
                                        self.list.options.errMsg = ['Something went wrong with the provided function.'];
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
                if ( null != self.list.options ) {
                    if ( undefined != self.list.options.type ) {
                        var ks = Object.keys(self.list[self.list.options.type]);
                        for (var i = ks.length - 1; i >= 0; i--) {
                            var k = ks[i];
                            if ( self.list[self.list.options.type][k] ) {
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
                if ( null != self.list.options ) {
                    if ( undefined != self.list.options.type ) {
                        var list = [];
                        var ks = Object.keys(self.list[self.list.options.type]);
                        for (var i = ks.length - 1; i >= 0; i--) {
                            var k = ks[i];
                            if ( self.list[self.list.options.type][k] ) {
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
            self.attr_rename = function (network, session_id, old_name, group) {
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
                            network: JSON.stringify(network),
                            attr_type: group,
                            attr_name: old_name,
                            attr_new_name: new_name
                        },
                        url: 's/'

                    }).
                        success(function (data) {
                            if ( 0 == data.err ) {
                                rootScope.$broadcast('load_in_canvas', data.net);
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
            self.attr_remove = function (network, session_id, old_name, group) {
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
                            network: JSON.stringify(network),
                            attr_type: group,
                            attr_name: old_name,
                        },
                        url: 's/'

                    }).
                        success(function (data) {
                            console.log(data);
                            if ( 0 == data.err ) {
                                rootScope.$broadcast('load_in_canvas', data.net);
                                self.do_attr(null);
                            }
                            qwait.resolve(data);
                        });

                    return q.promise;
                }
            };

            // GENERAL
            
            self.isArray = function (v) {
                return Array.isArray(v);
            };

            self.isObject = function (v) {
                return 'object' === typeof v;
            };

            /**
             * Resets service
             */
            self.reset_service = function () {
                self.list = {
                    label: null,
                    options: null,
                    edges: {},
                    nodes: {}
                };
            }; 

        };

    });

}());
