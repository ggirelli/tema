(function () {
    "use strict";

    define([], function () {

        return function (scope, model, http, timeout, rootScope, formChecker) {

        	scope.m = model;
            scope.m.formChecker = formChecker;
            
            /**
             * Functions to log out
             * @type {Object}
             */
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
	        };

            /**
             * Functions to create a session
             * @type {Object}
             */
            scope.create = {

                setDoing: function (val) {
                    scope.m.session_sys.create = {
                        doing: val,
                        title: null,
                        privacy: '',
                        protected: null,
                        pwd: null,
                        hatch: null,
                        err: {
                            title: false,
                            privacy: false,
                            pwd: false,
                            code: 0
                        }
                    }
                },

                isDoing: function () {
                    return( true === scope.m.session_sys.create.doing );
                },

                do: function () {
                    // Check for bot in the honeypot
                    if(scope.m.session_sys.create.hatch != null) {
                        return(false)
                    } else {
                        scope.m.session_sys.create.err.title = !scope.m.formChecker.user(
                            scope.m.session_sys.create.title
                        );
                        scope.m.session_sys.create.err.privacy = !scope.create.checkPrivacy(
                            scope.m.session_sys.create.privacy
                        );
                        if(true === scope.m.session_sys.create.protected) {
                            scope.m.session_sys.create.err.pwd = !scope.m.formChecker.password(
                                scope.m.session_sys.create.pwd
                            );
                        };


                        if(
                            !scope.m.session_sys.create.err.pwd &&
                            !scope.m.session_sys.create.err.title &&
                            !scope.m.session_sys.create.err.privacy
                        ) {
                            // Send form to the back-end
                            http({

                                method: 'POST',
                                data: {
                                    action: 'create_session',
                                    usr: rootScope.TEMAlogged.usr,
                                    title: scope.m.session_sys.create.title,
                                    privacy: scope.m.session_sys.create.privacy,
                                    protected: scope.m.session_sys.create.protected,
                                    pwd: scope.m.session_sys.create.pwd
                                },
                                url: 's/'

                            })
                                .success(function (data) {
                                    scope.m.session_sys.create.err.code = data.err;
                                    if( scope.create.isError(0) ) {
                                        scope.create.setDoing(false);
                                        document.location.hash = data.hash;
                                    }
                                });
                        }
                    }
                },

                isError: function (val) {
                    return( val === scope.m.session_sys.create.err.code );
                },

                checkPrivacy: function (val) {
                    switch(val) {
                        case 'public': case 'private': {
                            return(true);
                            break;
                        }
                        default:
                            return(false);
                    }
                }
            };

            /**
             * Functions that manage the user-page panels
             * @type {Object}
             */
            scope.panels = {

                loadOwned: function () {
                    http({

                        method: 'POST',
                        data: {
                            action: 'list_sessions',
                            type: 'owned',
                            usr: scope.m.logsys.logged.usr
                        },
                        url: 's/'

                    })
                        .success(function (data) {
                            if ( 0 == data.err ) {
                                scope.m.userpage.owned_list = data.list;
                            } else {
                                console.log('ERROR: Could not retrieve list of owned sessions.');
                            }
                        });
                },

                loadShared: function () {
                    http({

                        method: 'POST',
                        data: {
                            action: 'list_sessions',
                            type: 'shared',
                            usr: scope.m.logsys.logged.usr
                        },
                        url: 's/'

                    })
                        .success(function (data) {
                            console.log(data);
                            if ( 0 == data.err ) {
                                scope.m.userpage.shared_list = data.list;
                            } else {
                                console.log('ERROR: Could not retrieve list of shared sessions.');
                            }
                        });
                },

                loadHistory: function () {
                    http({

                        method: 'POST',
                        data: {
                            action: 'list_sessions',
                            type: 'history',
                            usr: scope.m.logsys.logged.usr
                        },
                        url: 's/'

                    })
                        .success(function (data) {
                            console.log(data);
                            if ( 0 == data.err ) {
                                scope.m.userpage.shared_list = data.list;
                            } else {
                                console.log('ERROR: Could not retrieve session history.');
                            }
                        });
                },

                loadAll: function () {
                    scope.panels.loadOwned();
                    scope.panels.loadShared();
                    scope.panels.loadHistory();
                },

                reload: function () {
                    scope.panels.loadAll();
                }

            };

            scope.panels.loadAll();

        };

    });

}());
