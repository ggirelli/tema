(function () {
    "use strict";

    define([], function () {

        return function (scope, model, http, timeout, rootScope) {

        	scope.m = model;

        	scope.out = {

        		setSigning: function (val) {
        			scope.m.logsys.out.doing = val;
        		},

        		isSigning: function () {
        			return(true === scope.m.logsys.out.doing);
        		},

        		sign: function () {
	        		scope.out.setSigning(true);

	        		timeout(function () {
	        			scope.out.setSigning(false);
	        			scope.m.logsys.logged = {
                            status: false,
                            usr: null
                        };
	        			rootScope.TEMAlogged = scope.m.logsys.logged;
	        		}, 1500);
	        	}
	        }

        };

    });

}());
