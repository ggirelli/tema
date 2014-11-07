(function () {
    "use strict";

    define([], function () {

        return function (routeProvider) {
                routeProvider

                    .when('/', {
                        templateUrl: 'user_side/src/app/mods/homepage/homepage.tpl.html'
                    })

                    .when('/upload/:id', {
                        templateUrl: 'user_side/src/app/mods/upload/upload.tpl.html'
                    })

                    .when('/interface/:id', {
                        templateUrl: 'user_side/src/app/mods/interface/interface.tpl.html'
                    })

                    .otherwise({
                        redirectTo: '/'
                    });
            };

    });

}());