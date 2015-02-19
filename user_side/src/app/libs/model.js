(function () {
    "use strict";

    define([], function () {

        return function () {
            var self = this;
            
            self.guestpage = {
                in: false,
                load: false,
                up:  false
            };

            self.session_id = null;
           	self.currentSession = null;

        };

    });

}());
