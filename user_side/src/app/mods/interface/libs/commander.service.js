(function () {
    "use strict";

    define([], function () {

        return function (q, http, timeout, mergeGroup) {
            var self = this;

            self.operation = {
                status: false
            };

            self.merge = mergeGroup;

            /**
             * Initializes the operation UI
             * @param  {String} name operation name
             */
            self.init_operation = function (name, net_list, session_id) {
                self.operation.name = name;
                self.operation.status = true;
                self.selected = {};

                if ( 'merge' == name ) {
                    self.merge.name_list = []
                    self.merge.list = [];
                    for (var i = 0; i < net_list.length; i++) {
                        var net = net_list[i];
                        self.merge.name_list.push(net.name);
                        if ( 1 == net.status ) {
                            self.merge.list.push(net);
                        }
                    }
                    self.merge.set_page(1);
                    self.merge.toggle(session_id);
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
            }

            // GENERAL
            
            self.reset_ui = function () {
                self.operation = {
                    status: false
                };
                self.merge.reset_service();
            };

        };

    });

}());
