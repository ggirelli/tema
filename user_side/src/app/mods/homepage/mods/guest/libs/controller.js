(function () {
    "use strict";

    define([], function () {

        return function (scope, model, http, timeout) {

        	scope.m = model;
        	
        	scope.up = {

    			setSigning: function(val) {
    				scope.m.guestpage.up = val;
    			},

    			isSigning: function() {
    				return(scope.m.guestpage.up);
    			},

    			sign: function() {

    			}

    		};

        	scope.in = {

    			setSigning: function(val) {
    				scope.m.guestpage.in = val;
    			},

    			isSigning: function() {
    				return(scope.m.guestpage.in);
    			},

    			sign: function() {

    			}

    		};

    		scope.load = {

    			setLoading: function(val) {
    				scope.m.guestpage.load = val;
    			},

    			isLoading: function() {
    				return(scope.m.guestpage.load);
    			},

    			load: function() {

    			}

    		};

        };

    });

}());
