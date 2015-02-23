(function () {
    "use strict";

    define([], function () {

        return function (scope, model, http, timeout, rootScope) {

        	scope.m = model;

        	scope.out = {

        		setSigning: function (val) {
        			scope.m.out.doing = val;
        		},

        		isSigning: function () {
        			return(true === scope.m.out.doing);
        		},

        		sign: function () {
	        		scope.out.setSigning(true);

	        		timeout(function () {
	        			scope.out.setSigning(false);
	        			scope.m.logged = false;
	        			rootScope.TEMAlogged = false;
	        		}, 1500);
	        	}
	        }

        };

    });

}());
