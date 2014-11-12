(function () {
    "use strict";

    define([], function () {

        return function (scope, model, routeParams, networks,
            panels, inspector, commander, canvas, settings) {

        	scope.m = model;
            scope.m.session_id = routeParams.id;

        	scope.inspector = inspector;
        	scope.panels = panels;
        	scope.commander = commander;
            scope.canvas = canvas;
            scope.settings = settings;

            /* Initialize network list */
            
            scope.networks = networks;
            scope.commander.get_network_list(scope.m.session_id).then(function (data) {
                console.log(data);
                if (0 != data['err'] ) {
                    document.location.hash = '#/';
                } else {
                    if ( 0 == data.list.length ) {
                        scope.networks.list = null;
                    } else {
                        scope.networks.list = data.list;
                    }
                }
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

            /* Initialize settings */

            // Check SIF
            scope.settings.is_sif(scope.m.session_id).then(function (data) {
                if ( true === data ) {
                    scope.settings.get_sif(scope.m.session_id).then(function (data) {
                        if ( 0 == data['err']) {
                            scope.settings.info.sif = data.sif;
                            if ( scope.settings.is_sif_ready() ) {
                                scope.settings.info.sif_keys = Object.keys(scope.settings.info.sif);
                            }
                        }
                    });
                }
            });

            // Read settings
            scope.settings._read(scope.m.session_id).then(function (data) {
                scope.settings.info.sif_sample_col = data['sif_sample_col'];
                scope.settings.info.node_thr = data['node_thr'];
                console.log(scope.settings.info);
            });

        };

    });

}());
