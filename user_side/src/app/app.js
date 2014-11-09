(function () {
    "use strict";

    var def_requirements = ['angular',
        './libs/model', './libs/controller', './libs/route_config',
        './mods/homepage/libs/controller', './mods/interface/libs/controller',
        './mods/interface/libs/inspector.service', './mods/interface/libs/panels.service',
        './mods/interface/libs/commander.service',
        './mods/uploader/libs/controller', './mods/uploader/libs/service', './mods/uploader/libs/directive',
        'angular-route', 'angular-animate', 'jquery'];

    define(def_requirements,
        function (angular, model, controller, routeConfig,
            homepageController, interfaceController,
            inspectorService, panelsService, commanderService,
            uploaderController, uploaderService, autoclickDirective) {
        
        angular.module('sogi', ['ngRoute']).
        	config(['$routeProvider', routeConfig]).

        	service('appModel', [model]).
        	controller('appController', ['$scope', 'appModel', controller]).

            controller('homepageController', ['$scope', 'appModel', '$http', '$timeout', homepageController]).
            
            service('inspectorService', [inspectorService]).
            service('panelsService', [panelsService]).
            service('commanderService', ['$q', '$http', commanderService]).
            controller('interfaceController', ['$scope', 'appModel', '$routeParams', 'panelsService', 'inspectorService', 'commanderService', interfaceController]).

            service('uploaderService', ['$timeout', uploaderService]).
            controller('uploaderController', ['$scope', 'appModel', '$routeParams', 'uploaderService', uploaderController]).

            directive('uploaderAutoclick', ['$parse', function ($parse) {

                return {
                    restrict: 'A',
                    link: autoclickDirective
                };

            }])

    });

}());
