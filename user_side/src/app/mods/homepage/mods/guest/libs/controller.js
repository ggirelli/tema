(function () {
    "use strict";

    define([], function () {

        return function (scope, model, http, timeout, rootScope, formChecker) {

        	scope.m = model;
            scope.m.formChecker = formChecker;
        	
        	scope.up = {

    			setSigning: function (val) {
    				scope.m.logsys.up = {
                        doing: val,
                        run: false,
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
    				return( true === scope.m.logsys.up.doing );
    			},

                setRunning: function (val) {
                    scope.m.logsys.up.run = val;
                },

                isRunning: function () {
                    return( true === scope.m.logsys.up.run );
                },

    			sign: function () {
                    // Check for bot in the honeypot
                    if(scope.m.logsys.up.hatch != null) {
                        return(false)
                    } else {
                        scope.up.setRunning(true);

                        scope.m.logsys.up.err = {
                            pwd: !scope.m.formChecker.password(scope.m.logsys.up.pwd),
                            email: !scope.m.formChecker.email(scope.m.logsys.up.email),
                            usr: !scope.m.formChecker.user(scope.m.logsys.up.usr)
                        }

                        if(
                            !scope.m.logsys.up.err.pwd &&
                            !scope.m.logsys.up.err.email &&
                            !scope.m.logsys.up.err.usr
                        ) {
                            // Send form to the back-end
                            http({

                                method: 'POST',
                                data: {
                                    action: 'register_user',
                                    user: scope.m.logsys.up.usr,
                                    password: scope.m.logsys.up.pwd,
                                    email: scope.m.logsys.up.email
                                },
                                url: 's/'

                            })
                                .success(function (data) {
                                    scope.up.setRunning(false);
                                    scope.m.logsys.up.err.code = data.err;
                                });
                        } else {
                            scope.up.setRunning(false);
                        }
                    }
    			},

                isError: function (val) {
                    return(val === scope.m.logsys.up.err.code);
                }

    		};

        	scope.in = {

    			setSigning: function (val) {
                    scope.m.logsys.in = {
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
    				return(scope.m.logsys.in.doing);
    			},

                sign: function () {
                    // Check for bot in the honeypot
                    if(scope.m.logsys.in.hatch != null) {
                        return(false)
                    } else {

                        // Send form to the back-end
                        http({

                            method: 'POST',
                            data: {
                                action: 'login_user',
                                user: scope.m.logsys.in.usr,
                                password: scope.m.logsys.in.pwd
                            },
                            url: 's/'

                        })
                            .success(function (data) {
                                scope.m.logsys.in.err.code = data.err;
                                if ( 0 === data.err ) {
                                    timeout(function() {
                                        scope.m.logsys.logged = {
                                            status: true,
                                            usr: scope.m.logsys.in.usr
                                        };
                                        rootScope.TEMAlogged = scope.m.logsys.logged;
                                        scope.in.setSigning(false);
                                    }, 1500)
                                }
                            });

                    }
                },

                isError: function (val) {
                    return( val === scope.m.logsys.in.err.code )
                }

    		};

    		scope.load = {

    			setLoading: function (val) {
    				scope.m.logsys.load.doing = val;
    			},

    			isLoading: function () {
    				return(scope.m.logsys.load.doing);
    			},

    			load: function () {

    			}

    		};

        };

    });

}());
