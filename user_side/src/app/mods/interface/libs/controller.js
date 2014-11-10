(function () {
    "use strict";

    define([], function () {

        return function (scope, model, routeParams, networks, panels, inspector, commander, canvas) {

        	scope.m = model;
            scope.m.session_id = routeParams.id;

        	scope.inspector = inspector;
        	scope.panels = panels;
        	scope.commander = commander;
            scope.canvas = canvas;

            /* Initialize network list */
            
            scope.networks = networks;
            scope.commander.get_network_list(scope.m.session_id).then(function (data) {
                scope.networks.list = data.list;
            });

            /**
             * Converts or load a network
             * @param  {Object} network from networks.list
             */
            scope.networks.init_file = function (network) {
                if ( 0 == network.status ) {
                    scope.networks.convert(network, scope.m.session_id);
                } else {
                    scope.canvas.load(network, scope.m.session_id)
                }

            }

            /* Initialize canvas */

            scope.canvas.init();

        };

    });

}());
