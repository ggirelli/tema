(function () {
    "use strict";

    define([], function () {

        return function (q, http, timeout, rootScope, mergeGroup, intersectGroup, subtractGroup) {
            var self = this;

            self.operation = {
                status: false
            };

            self.merge = mergeGroup;
            self.intersect = intersectGroup;
            self.subtract = subtractGroup;

            /**
             * Initializes the operation UI
             * @param  {String} name operation name
             */
            self.init_operation = function (name, net_list, session_id) {
                self.operation.name = name;
                self.operation.status = true;
                self.selected = {};

                if ( -1 != ['intersect', 'merge', 'subtract'].indexOf(name) ) {
                    self[name].name_list = []
                    self[name].list = [];
                    for (var i = 0; i < net_list.length; i++) {
                        var net = net_list[i];
                        self[name].name_list.push(net.name);
                        if ( 1 == net.status ) {
                            self[name].list.push(net);
                        }
                    }
                    self[name].set_page(1);
                    self[name].toggle(session_id);
                }
            };

            /**
             * @param  {String}  name operation name
             * @return {Boolean}      if the given operation is running
             */
            self.is_operation = function (name) {
                if ( null == name ) return undefined == self.operation.name;
                return name == self.operation.name;
            };

            /**
             * Reset commander
             */
            self.abort_operation = function () {
                self.operation = {
                    status: false
                };
            };

            // GROUP MERGE

            /**
             * Changes page of merge UI after checking the form
             * @param  {integer} index page
             */
            self.merge_set_page = function (index) {
                if ( 2 == index ) {
                    // Check new name
                    if ( undefined == self.merge.group.new_name || null == self.merge.group.new_name || '' == self.merge.group.new_name ) {
                        self.merge.errMsg = 'Please, provide a name.';
                        return;
                    } else if ( -1 != self.merge.name_list.indexOf(self.merge.group.new_name) ) {
                        self.merge.errMsg = 'Name alredy in use.';
                        return;
                    }

                    // Check number of selected networks
                    var c = 0;
                    var ks = Object.keys(self.merge.group.networks);
                    for (var i = ks.length - 1; i >= 0; i--) {
                        var k = ks[i];
                        if ( self.merge.group.networks[k] ) {
                            c++;
                        }
                    }

                    // Minimum of 2 selected networks, otherwise trigger error
                    if ( c >= 2 ) {
                        // Clear previous errors
                        self.merge.errMsg = undefined;

                        // (re-)Define vars for next page
                        self.merge.n_attr_identity = {};
                        self.merge.e_attr_identity = {};

                        // Go to next page
                        self.merge.set_page(index)
                    } else {
                        self.merge.errMsg = 'Select at least 2 networks.';
                    }
                } else if ( 3 == index ) {
                    // Check that at least 1 attribute was selected for NODES
                    var n = 0;
                    var nks = Object.keys(self.merge.n_attr_identity);
                    for (var i = nks.length - 1; i >= 0; i--) {
                        if ( self.merge.n_attr_identity[nks[i]] ) n++;
                    }

                    if ( n > 0 ) {
                        // Clear previous errors
                        self.merge.errMsg = undefined;

                        // (re-)Define vars for next page
                        self.merge.n_attr_behavior = {};
                        for (var i = self.merge.group.nodes.length - 1; i >= 0; i--) {
                            var node = self.merge.group.nodes[i];
                            if ( !self.merge.n_attr_identity[node] ) self.merge.n_attr_behavior[node] = 'ignore';
                        };
                        self.merge.e_attr_behavior = {};
                        for (var i = self.merge.group.edges.length - 1; i >= 0; i--) {
                            var edge = self.merge.group.edges[i];
                            if ( !self.merge.e_attr_identity[edge] ) self.merge.e_attr_behavior[edge] = 'ignore';
                        };

                        // Go to next page
                        self.merge.set_page(index)
                    } else {
                        self.merge.errMsg = 'Select at least ONE attribute for the nodes identity function.';
                    }
                } else if ( 4 == index ) {
                    // (re-)Define vars for next page
                    self.merge.add_node_count_attr = false;
                    self.merge.add_edge_count_attr = false;

                    // Go to next page
                    self.merge.set_page(index)
                }
            };

            /**
             * Runs the merge operation
             * @param  {string} session_id
             */
            self.apply_merge = function (session_id) {
                var qwait = q.defer();

                http({

                    method: 'POST',
                    data: {
                        action: 'networks_merge',
                        id: session_id,
                        new_name: self.merge.group.new_name,
                        networks: self.merge.get_selected_list(),
                        n_identity: self.merge.n_attr_identity,
                        e_identity: self.merge.e_attr_identity,
                        n_behavior: self.merge.n_attr_behavior,
                        e_behavior: self.merge.e_attr_behavior,
                        n_count_attr: self.merge.add_node_count_attr,
                        e_count_attr: self.merge.add_edge_count_attr
                    },
                    url: 's/'

                }).
                    success(function (data) {
                        if ( 0 == data.err ) {
                            rootScope.$broadcast('reload_network_list', session_id);
                            alert('Merged networks.');
                        }
                        qwait.resolve(data);
                    });

                self.reset_ui();
                return qwait.promise;
            };

            // GROUP INTERSECT
            
            /**
             * Changes page of merge UI after checking the form
             * @param  {integer} index page
             */
            self.intersect_set_page = function (index) {
                if ( 2 == index ) {
                    // Check new name
                    if ( undefined == self.intersect.group.new_name || null == self.intersect.group.new_name || '' == self.intersect.group.new_name ) {
                        self.intersect.errMsg = 'Please, provide a name.';
                        return;
                    } else if ( -1 != self.intersect.name_list.indexOf(self.intersect.group.new_name) ) {
                        self.intersect.errMsg = 'Name alredy in use.';
                        return;
                    }

                    // Check number of selected networks
                    var c = 0;
                    var ks = Object.keys(self.intersect.group.networks);
                    for (var i = ks.length - 1; i >= 0; i--) {
                        var k = ks[i];
                        if ( self.intersect.group.networks[k] ) {
                            c++;
                        }
                    }

                    // Minimum of 2 selected networks, otherwise trigger error
                    if ( c >= 2 ) {
                        // Clear previous errors
                        self.intersect.errMsg = undefined;

                        // (re-)Define vars for next page
                        self.intersect.n_attr_identity = {};
                        self.intersect.e_attr_identity = {};

                        // Go to next page
                        self.intersect.set_page(index)
                    } else {
                        self.intersect.errMsg = 'Select at least 2 networks.';
                    }
                } else if ( 3 == index ) {
                    // Check that at least 1 attribute was selected for NODES
                    var n = 0;
                    var nks = Object.keys(self.intersect.n_attr_identity);
                    for (var i = nks.length - 1; i >= 0; i--) {
                        if ( self.intersect.n_attr_identity[nks[i]] ) n++;
                    }

                    if ( n > 0 ) {
                        // Clear previous errors
                        self.intersect.errMsg = undefined;

                        // (re-)Define vars for next page
                        self.intersect.n_attr_behavior = {};
                        for (var i = self.intersect.group.nodes.length - 1; i >= 0; i--) {
                            var node = self.intersect.group.nodes[i];
                            if ( !self.intersect.n_attr_identity[node] ) self.intersect.n_attr_behavior[node] = 'ignore';
                        };
                        self.intersect.e_attr_behavior = {};
                        for (var i = self.intersect.group.edges.length - 1; i >= 0; i--) {
                            var edge = self.intersect.group.edges[i];
                            if ( !self.intersect.e_attr_identity[edge] ) self.intersect.e_attr_behavior[edge] = 'ignore';
                        };

                        // Go to next page
                        self.intersect.set_page(index)
                    } else {
                        self.intersect.errMsg = 'Select at least ONE attribute for the nodes identity function.';
                    }
                }
            };

            /**
             * Runs the intersect operation
             * @param  {string} session_id
             */
            self.apply_intersect = function (session_id) {
                var qwait = q.defer();

                http({

                    method: 'POST',
                    data: {
                        action: 'networks_intersect',
                        id: session_id,
                        new_name: self.intersect.group.new_name,
                        networks: self.intersect.get_selected_list(),
                        n_identity: self.intersect.n_attr_identity,
                        e_identity: self.intersect.e_attr_identity,
                        n_behavior: self.intersect.n_attr_behavior,
                        e_behavior: self.intersect.e_attr_behavior,
                    },
                    url: 's/'

                }).
                    success(function (data) {
                        if ( 0 == data.err ) {
                            rootScope.$broadcast('reload_network_list', session_id);
                            alert('Intersected networks.');
                        }
                        qwait.resolve(data);
                    });

                self.reset_ui();
                return qwait.promise;
            };

            // GROUP SUBTRACT

            /**
             * Changes page of merge UI after checking the form
             * @param  {integer} index page
             */
            self.subtract_set_page = function (index, session_id) {
                if ( 2 == index ) {
                    // Check new name
                    if ( undefined == self.subtract.group.new_name || null == self.subtract.group.new_name || '' == self.subtract.group.new_name ) {
                        self.subtract.errMsg = 'Please, provide a name.';
                        return;
                    } else if ( -1 != self.subtract.name_list.indexOf(self.subtract.group.new_name) ) {
                        self.subtract.errMsg = 'Name alredy in use.';
                        return;
                    }

                    // Check minuend network
                    if ( undefined == self.subtract.group.minuend ) {
                        self.subtract.errMsg = 'Select the minuend networks.';
                    } else {
                        // Clear previous errors
                        self.subtract.errMsg = undefined;

                        // Go to next page
                        self.subtract.set_page(index)
                    }
                } else if ( 3 == index ) {
                    // Check number of selected networks
                    var c = 0;
                    var ks = Object.keys(self.subtract.group.networks);
                    for (var i = ks.length - 1; i >= 0; i--) {
                        var k = ks[i];
                        if ( self.subtract.group.networks[k] ) {
                            c++;
                        }
                    }

                    // Minimum of 1 selected network, otherwise trigger error
                    if ( c >= 1 ) {
                        // Clear previous errors
                        self.subtract.errMsg = undefined;

                        // (re-)Define vars for next page
                        self.subtract.n_attr_identity = {};
                        self.subtract.e_attr_identity = {};

                        // Go to next page
                        self.subtract.set_page(index)
                    } else {
                        self.subtract.errMsg = 'Select at least 1 networks.';
                    }
                } else if ( 4 == index ) {
                    // Check that at least 1 attribute was selected for NODES
                    var n = 0;
                    var nks = Object.keys(self.subtract.n_attr_identity);
                    for (var i = nks.length - 1; i >= 0; i--) {
                        if ( self.subtract.n_attr_identity[nks[i]] ) n++;
                    }

                    if ( n > 0 ) {
                        // Clear previous errors
                        self.subtract.errMsg = undefined;

                        // (re-)Define vars for next page
                        self.subtract.n_attr_behavior = {};
                        for (var i = self.subtract.group.nodes.length - 1; i >= 0; i--) {
                            var node = self.subtract.group.nodes[i];
                            if ( !self.subtract.n_attr_identity[node] ) self.subtract.n_attr_behavior[node] = 'ignore';
                        };
                        self.subtract.e_attr_behavior = {};
                        for (var i = self.subtract.group.edges.length - 1; i >= 0; i--) {
                            var edge = self.subtract.group.edges[i];
                            if ( !self.subtract.e_attr_identity[edge] ) self.subtract.e_attr_behavior[edge] = 'ignore';
                        };

                        // Act
                        self.apply_subtract(session_id);
                    } else {
                        self.subtract.errMsg = 'Select at least ONE attribute for the nodes identity function.';
                    }                    
                }
            };

            /**
             * @return {Object} The minuend network
             */
            self.subtract.get_minuend = function () {
                for (var i = self.subtract.list.length - 1; i >= 0; i--) {
                    var network = self.subtract.list[i];
                    if ( self.subtract.group.minuend == network.name ) return(network);
                };
            };

            /**
             * Runs the subtract operation
             * @param  {string} session_id
             */
            self.apply_subtract = function (session_id) {
                var qwait = q.defer();

                http({

                    method: 'POST',
                    data: {
                        action: 'networks_subtract',
                        id: session_id,
                        new_name: self.subtract.group.new_name,
                        minuend: self.subtract.group.minuend,
                        networks: self.subtract.get_selected_list(),
                        n_identity: self.subtract.n_attr_identity,
                        e_identity: self.subtract.e_attr_identity
                    },
                    url: 's/'

                }).
                    success(function (data) {
                        console.log(data);
                        if ( 0 == data.err ) {
                            rootScope.$broadcast('reload_network_list', session_id);
                            alert('Subtracted networks.');
                        }
                        qwait.resolve(data);
                    });

                self.reset_ui();
                return qwait.promise;
            };

            // GENERAL
            
            /**
             * Resets commander UI
             */
            self.reset_ui = function () {
                self.operation = {
                    status: false
                };
                self.merge.reset_service();
                self.intersect.reset_service();
            };

        };

    });

}());
