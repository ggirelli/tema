(function () {
    "use strict";

    define([], function () {

        return function (scope, model, routeParams, uploader) {

        	scope.m = model;
        	scope.m.session_id = routeParams.id;

        	scope.uploader = uploader;

        };

    });

}());
