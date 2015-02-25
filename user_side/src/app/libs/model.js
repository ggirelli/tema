(function () {
    "use strict";

    define([], function () {

        return function () {
            var self = this;
            
            self.logsys = {
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
                },

                confrm: {
                    token: null,
                    err: null
                },

                out: {
                    doing: false
                },

                logged: {
                    status: false,
                    usr: null
                }
            };

            self.session_sys = {
                create: {
                    doing: false,
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
            };

            self.userpage = {
                owned_list: [],
                shared_list: [],
                history_list: []
            }

            self.session_id = null;
            self.session_error = true;
            self.session_protected = false;
            
           	self.currentSession = null;

        };

    });

}());
