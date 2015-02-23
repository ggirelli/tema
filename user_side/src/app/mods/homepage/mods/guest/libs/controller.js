(function () {
    "use strict";

    define([], function () {

        return function (scope, model, http, timeout, rootScope, formChecker) {

        	scope.m = model;
            scope.m.formChecker = formChecker;
        	
        	scope.up = {

    			setSigning: function (val) {
    				scope.m.guestpage.up = {
                        doing: val,
                        usr: null,
                        pwd: null,
                        email: null,
                        hatch: null,
                        err: {
                            pwd: false,
                            usr: false,
                            email: false,
                            code: null
                        }
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
                                    scope.m.guestpage.up.err.code = data.err;
                                });
                        }
                    }
    			},

                isError: function (val) {
                    return(val === scope.m.guestpage.up.err.code);
                }

    		};

        	scope.in = {

    			setSigning: function (val) {
                    scope.m.guestpage.in = {
                        doing: val,
                        usr: null,
                        pwd: null,
                        hatch: null,
                        err: {
                            code: null
                        }
                    };
    			},

    			isSigning: function () {
    				return(scope.m.guestpage.in.doing);
    			},

                sign: function () {
                    // Check for bot in the honeypot
                    if(scope.m.guestpage.in.hatch != null) {
                        return(false)
                    } else {

                        // Send form to the back-end
                        http({

                            method: 'POST',
                            data: {
                                action: 'login_user',
                                user: scope.m.guestpage.in.usr,
                                password: scope.m.guestpage.in.pwd
                            },
                            url: 's/'

                        })
                            .success(function (data) {
                                scope.m.guestpage.in.err.code = data.err;
                                if ( 0 === data.err ) {
                                    timeout(function() {
                                        rootScope.temaLogged = true;
                                        scope.m.logged = true;
                                        scope.in.setSigning(false);
                                    }, 1500)
                                }
                            });

                    }
                },

                isError: function (val) {
                    return( val === scope.m.guestpage.in.err.code )
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
