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
                    hatch: null,
                    err: {
                        code: null
                    }
                },
                up: {
                    doing: false,
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
                },
                load: {
                    doing: false,
                    token: null,
                    hatch: null
                }
            };

            self.confrm = {
                token: null,
                err: null
            };

            self.out = {
                doing: false
            };

            self.logged = false;

            self.session_id = null;
           	self.currentSession = null;

        };

    });

}());
