(function () {
    "use strict";

    var def_requirements = ['angular',
        './libs/model', './libs/controller', './libs/route_config',
        './mods/homepage/libs/controller', './mods/interface/libs/controller',
        './mods/interface/libs/inspector.service', './mods/interface/libs/panels.service',
        'angular-route', 'angular-animate'];

    define(def_requirements,
        function (angular, model, controller, routeConfig,
            homepageController, interfaceController,
            inspectorService, panelsService) {
        
        angular.module('sogi', ['ngRoute']).
        	config(['$routeProvider', routeConfig]).

        	service('appModel', [model]).
        	controller('appController', ['$scope', 'appModel', controller]).

            controller('homepageController', ['$scope', 'appModel', '$http', '$timeout', homepageController]).
            
            service('inspectorService', [inspectorService]).
            service('panelsService', [panelsService]).
            controller('interfaceController', ['$scope', 'appModel', 'panelsService', 'inspectorService', interfaceController])

    });

}());
