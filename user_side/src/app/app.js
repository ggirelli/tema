(function () {
    "use strict";

    var def_requirements = ['angular',
        './libs/model', './libs/controller', './libs/route_config',
        './mods/homepage/libs/controller', './mods/interface/libs/controller',
        './mods/interface/libs/network.service',
        './mods/interface/libs/inspector.service', './mods/interface/libs/panels.service',
        './mods/interface/libs/commander.service', './mods/interface/libs/canvas.service',
        './mods/interface/libs/settings.service',
        './mods/uploader/libs/controller', './mods/uploader/libs/service', 
        './mods/uploader/libs/directive', './mods/uploader/libs/submit_form.directive',
        'angular-route', 'angular-animate', 'jquery'];

    define(def_requirements,
        function (angular, model, controller, routeConfig,
            homepageController, interfaceController,
            networkService,
            inspectorService, panelsService,
            commanderService, canvasService,
            settingsService,
            uploaderController, uploaderService,
            autoclickDirective, submitformDirective) {
        
        angular.module('sogi', ['ngRoute']).
        	config(['$routeProvider', routeConfig]).

        	service('appModel', [model]).
        	controller('appController', ['$scope', 'appModel', controller]).

            controller('homepageController', ['$scope', 'appModel', '$http', '$timeout', homepageController]).
            
            service('canvasService', ['$q', '$http', canvasService]).
            service('commanderService', ['$q', '$http', commanderService]).
            service('inspectorService', [inspectorService]).
            service('networkService', ['$q', '$http', networkService]).
            service('panelsService', [panelsService]).
            service('settingsService', ['$q', '$http', '$rootScope', settingsService]).
            controller('interfaceController', ['$scope', 'appModel', '$routeParams', 'networkService',
                'panelsService', 'inspectorService', 'commanderService', 'canvasService', 'settingsService', interfaceController]).

            service('uploaderService', ['$http', '$q', '$timeout', uploaderService]).
            controller('uploaderController', ['$scope', 'appModel', '$routeParams', 'uploaderService', uploaderController]).

            directive('uploaderAutoclick', ['$parse', function ($parse) { return { restrict: 'A', link: autoclickDirective }; }]).
            directive('submitForm', [function () { return { restrict: 'A', link: submitformDirective }; }])

    });

}());
