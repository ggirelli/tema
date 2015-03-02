(function () {
    "use strict";

    define([], function () {

        return function (q, scope, model, routeParams, networks,
            panels, inspector, commander, canvas, settings,
            sessions, uploader) {

        	scope.m = model;
            scope.m.session_id = routeParams.id;
            self.session_error = true;
            scope.m.session_protected = false;
            scope.m.uploader_type = "0";

            scope.networks = networks;
        	scope.inspector = inspector;
        	scope.panels = panels;
        	scope.commander = commander;
            scope.canvas = canvas;
            scope.settings = settings;
            scope.sessions = sessions;
            scope.uploader = uploader;

            /*--------------------------------------*/
            /* Check session privacy and protection */
            /*--------------------------------------*/

            scope.waitLogin = q.defer();
            scope.enterProtected = function (wait) {
                scope.sessions.enter(scope.m.session_id).then(function (errorCode) {
                    switch(errorCode) {
                        case 6: {
                            scope.m.session_protected = true;
                            scope.m.session_error = true;
                            break;
                        }
                        case 0: {
                            wait.resolve();
                            break;
                        }
                        default: {
                            scope.m.session_protected = false;
                            scope.m.session_error = true;
                        }
                    }
                });
            };
            scope.frontEnter = function () {
                scope.enterProtected(scope.waitLogin);
            };
            scope.enterProtected(scope.waitLogin);

            scope.waitLogin.promise.then(function () {
                scope.m.session_error = false;
                scope.m.session_protected = false;

                /*------------------------*/
                /* Update session history */
                /*------------------------*/

                if ( scope.m.logsys.logged.status ) {

                    scope.sessions.update_history(
                        scope.m.session_id,
                        scope.m.logsys.logged.usr
                    );

                } else {
                    console.log(2);
                }

                /*-------------------------*/
                /* Initialize network list */
                /*-------------------------*/

                scope.networks.get_list(scope.m.session_id).then(function (data) {
                    console.log(data);
                    if (0 != data['err'] ) {
                        document.location.hash = '#/';
                    } else {
                        if ( 0 == data.list.length ) {
                            scope.networks.list = null;
                        } else {
                            scope.networks.list = data.list;
                        }

                        /*-------------------*/
                        /* Initialize canvas */
                        /*-------------------*/

                        scope.canvas.init();

                        /*---------------------*/
                        /* Initialize settings */
                        /*---------------------*/

                        // Read settings
                        scope.settings._read(scope.m.session_id);

                        // Check SIF
                        scope.settings.is_file(scope.m.session_id, 'sif').then(function (data) {
                            
                            if ( true === data.res ) {
                                scope.settings.get_sif(scope.m.session_id).then(function (data) {

                                    if ( 0 == data['err']) {
                                        scope.settings.info.sif = data.sif;

                                        if ( scope.settings.is_sif_ready() ) {
                                            scope.settings.info.sif_keys = Object.keys(scope.settings.info.sif);
                                            scope.$broadcast('apply_sif', scope.settings.info);
                                        }
                                    }
                                });
                            }
                        });

                        // Check GOs
                        scope.settings.is_file(scope.m.session_id, 'goa').then(function (data) {
                            if ( true === data.res ) {
                                if ( 0 == data.data.err ) {
                                    scope.settings.info.goa = true;
                                }
                            }
                        });
                        scope.settings.is_file(scope.m.session_id, 'gob').then(function (data) {
                            if ( true === data.res ) {
                                if ( 0 == data.data.err ) {
                                    scope.settings.info.gob = true;
                                }
                            }
                        });
                        scope.settings.is_go_mapped(scope.m.session_id);
                    }
                });

                // Trigger apply_sif event
                scope.$on('trigger_apply_sif', function () {
                    settings.trigger_apply_sif();
                });

                // React to reload_network_list event
                scope.$on('reload_network_list', function (e, session_id) {
                    scope.networks.reload_list(session_id);
                });

                /*--------*/
                /* Canvas */
                /*--------*/

                scope.$on('load_in_canvas', function (event, network) {
                    scope.canvas.current = network;
                    scope.canvas.filtered = undefined;
                    scope.canvas.visualized = true;
                    scope.canvas.visualization = $.extend(true, {}, network);
                    cy.load(network);
                });


                /*----------*/
                /* Settings */
                /*----------*/

                // React to apply_sif event
                scope.$on('apply_sif', function (e, info) {
                    scope.networks.apply_sif(info);
                });

                /*-----------*/
                /* Inspector */
                /*-----------*/

                // React to inspect_network
                scope.$on('inspect_network', function (e, network) {
                    scope.inspector.load_network(network);
                });

                // React to inspect_node
                scope.$on('inspect_node', function (e, node) {
                    scope.inspector.load_node(node);
                });

                // React to inspect_edge
                scope.$on('inspect_edge', function (e, edge) {
                    scope.inspector.load_edge(edge);
                });

                // Reacto to close_inspector
                scope.$on('close_inspector', function (e) {
                    scope.inspector.close();
                });

                /*----------------*/
                /* General events */
                /*----------------*/

                scope.$on('reset-panels', function (e) {
                    scope.networks.reset_ui();
                    scope.commander.reset_ui();
                    scope.canvas.reset_ui();
                });

            });

            /**
             * Converts or load a network
             * @param  {Object} network from networks.list
             */
            scope.networks.init_file = function (network) {
                if ( 0 == network.status ) {
                    scope.networks.convert(network, scope.m.session_id, scope.settings.info.default_layout);
                } else {

                    if ( network.name == scope.canvas.current_data.name ) {
                        scope.canvas.postload();
                    } else {
                        if ( network.data.v_count <= scope.settings.info.node_thr ) {
                            scope.canvas.load(network, scope.m.session_id);
                        } else {
                            scope.canvas.preload(network, scope.m.session_id);
                        }
                    }
                }
            };

            scope.toggleUploader = function () {
                scope.m.uploading = !scope.m.uploading;
                scope.m.uploader_type = "0";
                scope.uploader.abort();
                scope.networks.reload_list(scope.m.session_id)

                if ( !scope.m.uploading ) scope.canvas.init();
            };

            /**
             * [resetApp description]
             * @return {[type]} [description]
             */
            scope.resetApp = function () {
                document.location.hash = '#/';
            };

        };

    });

}());
