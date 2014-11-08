(function () {
    "use strict";

    define([], function () {

        return function (scope, model, routeParams, panels, inspector, commander) {

        	scope.m = model;
            scope.m.session_id = routeParams.id;

        	scope.inspector = inspector;
        	scope.panels = panels;
        	scope.commander = commander;

        	scope.networks = {};
            scope.networks.list = commander.get_network_list(scope.m.session_id);

        };

    });

}());