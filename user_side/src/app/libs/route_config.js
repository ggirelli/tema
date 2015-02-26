(function () {
    "use strict";

    define([], function () {

        return function (routeProvider) {
                routeProvider

                    .when('/', {
                        templateUrl: 'user_side/src/app/mods/homepage/tpl.html'
                    })

                    .when('/upload/:id', {
                        templateUrl: 'user_side/src/app/mods/uploader/tpl.html'
                    })

                    .when('/interface/:id', {
                        templateUrl: 'user_side/src/app/mods/interface/tpl.html'
                    })

                    .when('/activation/:id', {
                        templateUrl: 'user_side/src/app/mods/user_activate/tpl.html'
                    })

                    .when('/help', {
                        templateUrl: 'user_side/src/app/mods/help/tpl.html'
                    })

                    .otherwise({
                        redirectTo: '/'
                    });
            };

    });

}());
