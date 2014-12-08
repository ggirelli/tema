(function () {
    "use strict";

    define([], function () {

        return function (q, http, filters) {
            var self = this;

            self.filters = filters;

            self.network = undefined;
            self.start_node = undefined;
            self.mode = undefined;
            self.token = undefined;

            /**
             * Loads network in the navigator (and its filter manager)
             * @param  {Object} network
             * @param  {String} token
             */
            self.init = function (network, token) {
                if ( undefined == network ) return;

                self.set_token(token);
                self.load(network);
                self.filters.init(network, token);
            };

            /**
             * Stores the service token
             */
            self.set_token = function (token) {
                self.token = token;
            };

            /**
             * Loads the network in the service
             * @param  {Object} network
             */
            self.load = function (network) {
                self.network = network;
            };

            /**
             * Retrieve node IDs
             * @return {Array} Node IDs
             */
            self.get_node_ids = function () {
                if ( undefined == self.network ) return [];

                var node_ids = [];

                for (var i = self.network.nodes.length - 1; i >= 0; i--) {
                    var node = self.network.nodes[i];

                    node_ids.push(node.data.id);
                }

                return node_ids;
            };

            /**
             * Filters and ID collection
             * @param  {Array} ids
             * @param  {Array} rm_ids IDs to remove
             * @return {Array}        Filtered ids
             */
            self.filter_node_ids = function (ids, rm_ids) {
                if ( undefined == ids ) return [];
                if ( 0 == rm_ids.length ) return ids;
                
                var filtered = []
                for (var i = ids.length - 1; i >= 0; i--) {
                    var id = ids[i];
                    if ( -1 != rm_ids.indexOf(id) ) {
                        filtered.push(id);
                    }
                }
                
                return filtered;
            };

            self.reset_service = function () {
                self.start_node = undefined;
                self.mode = undefined;
                self.token = undefined;
                self.network = undefined;
                self.filters.reset_service();
            };

        };

    });

}());
