(function () {
    "use strict";

    define([], function () {

        return function (scope, model, routeParams, panels, inspector, commander) {

        	scope.m = model;
            scope.m.session_id = routeParams.id;

        	scope.inspector = inspector;
        	scope.panels = panels;
        	scope.commander = commander;

            scope.networks = {
                list: null
            };
            scope.commander.get_network_list(scope.m.session_id).then(function (data) {
                scope.networks.list = data.list;
            });

        };

    });

}());
