(function () {
    "use strict";

    define([], function () {

        return function (scope, model, routeParams) {

        	scope.m = model;
        	scope.m.session_id = routeParams.id;

        };

    });

}());