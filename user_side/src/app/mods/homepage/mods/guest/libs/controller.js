(function () {
    "use strict";

    define([], function () {

        return function (scope, model, http, timeout, formChecker) {

        	scope.m = model;
            scope.m.formChecker = formChecker;
        	
        	scope.up = {

    			setSigning: function(val) {
    				scope.m.guestpage.up = {
                        doing: val,
                        usr: null,
                        pwd: null,
                        email: null,
                        hatch: null
                    };
    			},

    			isSigning: function() {
    				return(scope.m.guestpage.up.doing);
    			},

    			sign: function() {
                    // Check for bot in the honeypot
                    if(scope.m.guestpage.up.hatch != null) {
                        return(false)
                    } else {
                        var check = {
                            pwd: scope.m.formChecker.password(scope.m.guestpage.up.pwd),
                            email: scope.m.formChecker.email(scope.m.guestpage.up.email)
                        }
                        if (!check.pwd) {
                            // Warn that pwd is not correct
                        }
                        if (!check.email) {
                            // Warn that email is not correct
                        }
                        if (check.pwd && check.email) {
                            // Send form to the back-end
                        }
                    }
    			}

    		};

        	scope.in = {

    			setSigning: function(val) {
    				scope.m.guestpage.in.doing = val;
    			},

    			isSigning: function() {
    				return(scope.m.guestpage.in.doing);
    			},

    			sign: function() {

    			}

    		};

    		scope.load = {

    			setLoading: function(val) {
    				scope.m.guestpage.load.doing = val;
    			},

    			isLoading: function() {
    				return(scope.m.guestpage.load.doing);
    			},

    			load: function() {

    			}

    		};

        };

    });

}());
