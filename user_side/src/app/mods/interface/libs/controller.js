(function () {
    "use strict";

    define([], function () {

        return function (scope, model, panels, inspector) {

        	scope.m = model;

        	scope.inspector = inspector;
        	scope.panels = panels;

        };

    });

}());