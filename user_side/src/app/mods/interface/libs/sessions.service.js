(function () {
    "use strict";

    define([], function () {

        return function (q, http, rootScope) {
            var self = this;

            self.front_password = null;

            self.enter = function (session_id) {
                var qwait = q.defer();

                var user = null;
                if ( undefined != rootScope.TEMAlogged ) {
                    user = rootScope.TEMAlogged.usr;
                }

                http({

                    method: 'POST',
                    data: {
                        action: 'enter_session',
                        seed: session_id,
                        usr: user,
                        pwd: self.front_password
                    },
                    url: 's/'

                })
                    .success(function (data) {
                        self.front_password = null;
                        qwait.resolve(data.err);
                    });

                return(qwait.promise);
            };

            self.update_history = function (session_id, usr) {
                var qwait = q.defer(); 

                http({

                    method: 'POST',
                    data: {
                        action: 'update_history',
                        usr: usr,
                        seed: session_id
                    },
                    url: 's/'

                })
                    .success(function (data) {
                        if ( 0 != data.err ) {
                            console.log("ERROR: could not update user session history.");
                        }
                        qwait.resolve(data);
                    });

                return qwait.promise;
            };
        }

    });

}());
