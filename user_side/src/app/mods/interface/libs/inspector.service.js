(function () {
    "use strict";

    define([], function () {

        return function () {
            var self = this;

            self.inspecting = {
                network: null,
                node: null,
                edge: null
            };
        	
            /**
             * Visualization status
             * @type {Boolean}
             */
            self.show = false;

            /**
             * Opens/closes the inspector
             */
            self.toggle = function () {
                self.show = !self.show;
            };

            /**
             * Closes the inspectore
             */
            self.close = function () {
                self.show = false;
                self.inspecting = {
                    network: null,
                    node: null,
                    edge: null
                };
            };

            /**
             * Opens the inspector
             */
            self.open = function () {
                self.show = true;
            };

            /**
             * Loads a network data in the inspector
             * @param  {Object} network
             */
            self.load_network = function (network) {
                self.reset_inspecting()
                self.open();
                if ( 0 == network.data.v_count ) {
                    network.data.v_attributes = ["NA"];
                }
                if ( 0 == network.data.e_count ) {
                    network.data.e_attributes = ["NA"];
                }
                self.inspecting.network = network;
                
                network.data.v_attributes_array = Array.isArray(network.data.v_attributes);
                network.data.e_attributes_array = Array.isArray(network.data.e_attributes);
            };

            /**
             * Loads a node in the inspector
             * @param  {Object} node
             */
            self.load_node = function (node) {
                self.reset_inspecting()
                self.open();
                self.inspecting.node = node._private.data;
            };

            /**
             * Loads an edge in the inspector
             * @param  {Object} edge
             */
            self.load_edge = function (edge) {
                self.reset_inspecting()
                self.open();
                self.inspecting.edge = edge._private.data;
            };

            /**
             * @param  {String}  what is inspecting
             * @return {Boolean}      if inspecting 'what'
             */
            self.is_inspecting = function (what) {
                return null != self.inspecting[what];
            };

            /**
             * Resets self.inspecting variable
             */
            self.reset_inspecting = function () {
                self.inspecting = {
                    network: null,
                    node: null,
                    edge: null
                };
            };

        };

    });

}());
