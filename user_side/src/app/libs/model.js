(function () {
    "use strict";

    define([], function () {

        return function () {
            var self = this;
            
            self.guestpage = {
                in: {
                    doing: false,
                    usr: null,
                    pwd: null,
                    hatch: null
                },
                up: {
                    doing: false,
                    usr: null,
                    pwd: null,
                    email: null,
                    hatch: null,
                    err: {
                        pwd: false,
                        usr: false,
                        email: false
                    }
                },
                load: {
                    doing: false,
                    token: null,
                    hatch: null
                }
            };

            self.session_id = null;
           	self.currentSession = null;

        };

    });

}());
