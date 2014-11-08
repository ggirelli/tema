(function () {
    "use strict";

    define([], function () {

        return function (scope, model, panels, animate) {

        	scope.m = model;

        	// Panels service
        	scope.panels = panels;

        };

    });

}());