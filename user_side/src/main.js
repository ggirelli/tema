(function () {

    'use strict';

    requirejs.config({

        baseUrl: '/public_html/SOGIv020/user_side/',

        paths: {
            // Folders
            'app': 'src/app',
            'vendor': 'vendor',
            // Libraries
            'angular': 'vendor/js/angular',
            'angular-route': 'vendor/js/angular-route.min',
            'bootstrap': 'vendor/js/bootstrap.min',
            'cytoscape': 'vendor/js/cytoscape.min',
            'jquery': 'vendor/js/jquery.min'
        },
        shim: {
            // Vendor libraries.
            'angular': {
                'exports': 'angular'
            },
            'angular-route': {
                'exports': 'ngRoute',
                'deps': ['angular']
            },
            'jquery': {
                'exports': 'jquery'
            },
            'cytoscape': {
                'exports': 'cytoscape'
            }
        }
    });

    requirejs(['angular', 'angular-route', 'app/app'], function (angular) {

        // Angular bootstrapping
        angular.element(document).ready(function () {
            angular.bootstrap(document, ['sogi']);
        });

    });

}());
