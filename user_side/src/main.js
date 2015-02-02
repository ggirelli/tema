(function () {

    'use strict';

    requirejs.config({

        baseUrl: '/public_html/TEA/user_side/',

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
            'jquery': 'vendor/js/jquery.min',
            'dropzone': 'vendor/js/dropzone'
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
            },
            'dropzone': {
                'exports': 'dropzone'
            }
        }
    });

    requirejs(['angular', 'angular-animate', 'angular-route', 'jquery', 'cytoscape', 'dropzone', 'app/app'], function (angular) {

        // Angular bootstrapping
        angular.element(document).ready(function () {
            angular.bootstrap(document, ['tea']);
        });

    });

}());
