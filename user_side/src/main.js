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
            'angular-animate': 'vendor/js/angular-animate.min',
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
            'angular-animate': {
                'exports': 'ngAnimate',
                'deps': ['angular']
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

    requirejs(['angular', 'angular-animate', 'angular-route', 'cytoscape', 'jquery', 'app/app'], function (angular) {

        // Angular bootstrapping
        angular.element(document).ready(function () {
            angular.bootstrap(document, ['tea']);
        });

    });

}());
