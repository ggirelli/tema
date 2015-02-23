(function () {
    "use strict";

    define([], function () {

        return function (scope, model, http, timeout, routeParams) {

        	scope.m = model;
            scope.m.logsys.confrm.token = routeParams.id;

            http({

                method: 'POST',
                data: {
                    action: 'confirm_user',
                    token: scope.m.logsys.confrm.token
                },
                url: 's/'

            })
                .success(function(data) {
                    scope.m.logsys.confrm.err = data.err;
                });

            scope.isError = function (v) {
                return( v === scope.m.logsys.confrm.err );
            };

        };

    });

}());
