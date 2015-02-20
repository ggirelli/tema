(function () {
    "use strict";

    define([], function () {

        return function (scope, model, http, timeout, formChecker) {

        	scope.m = model;
            scope.m.formChecker = formChecker;
        	
        	scope.up = {

    			setSigning: function (val) {
    				scope.m.guestpage.up = {
                        doing: val,
                        usr: null,
                        pwd: null,
                        email: null,
                        hatch: null
                    };
    			},

    			isSigning: function () {
    				return(scope.m.guestpage.up.doing);
    			},

    			sign: function () {
                    // Check for bot in the honeypot
                    if(scope.m.guestpage.up.hatch != null) {
                        return(false)
                    } else {
                        scope.m.guestpage.up.err = {
                            pwd: !scope.m.formChecker.password(scope.m.guestpage.up.pwd),
                            email: !scope.m.formChecker.email(scope.m.guestpage.up.email),
                            usr: !scope.m.formChecker.user(scope.m.guestpage.up.usr)
                        }

                        if(
                            !scope.m.guestpage.up.err.pwd &&
                            !scope.m.guestpage.up.err.email &&
                            !scope.m.guestpage.up.err.usr
                        ) {
                            // Send form to the back-end
                            http({

                                method: 'POST',
                                data: {
                                    action: 'register_user',
                                    user: scope.m.guestpage.up.usr,
                                    password: scope.m.guestpage.up.pwd,
                                    email: scope.m.guestpage.up.email
                                },
                                url: 's/'

                            })
                                .success(function (data) {
                                    console.log(data);
                                });
                        }
                    }
    			}

    		};

        	scope.in = {

    			setSigning: function (val) {
    				scope.m.guestpage.in.doing = val;
    			},

    			isSigning: function () {
    				return(scope.m.guestpage.in.doing);
    			},

    			sign: function () {

    			}

    		};

    		scope.load = {

    			setLoading: function (val) {
    				scope.m.guestpage.load.doing = val;
    			},

    			isLoading: function () {
    				return(scope.m.guestpage.load.doing);
    			},

    			load: function () {

    			}

    		};

        };

    });

}());
