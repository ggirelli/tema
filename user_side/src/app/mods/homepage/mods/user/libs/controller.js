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
                        token: null,
                        public: null,
                        protected: null,
                        password: null
                    }
                },

                isDoing: function () {
                    return( true === scope.m.session_sys.create.doing );
                },

                do: function () {
                    console.log(1);
                }

            };

        };

    });

}());
