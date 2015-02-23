(function () {
    "use strict";

    define([], function () {

        return function (scope, model, http, timeout, routeParams) {

        	scope.m = model;
            scope.m.confrm.token = routeParams.id;

            http({

                method: 'POST',
                data: {
                    action: 'confirm_user',
                    token: scope.m.confrm.token
                },
                url: 's/'

            })
                .success(function(data) {
                    scope.m.confrm.err = data.err;
                    console.log(data);
                });

            scope.isError = function(v) {
                return( v === scope.m.confrm.err );
            };

        };

    });

}());
