(function () {
    "use strict";

    define([], function () {

        return function () {
            var self = this;
            
            self.homepage = {
                in: false,
                load: false,
                up:  false
            };

            self.session_id = null;
           	self.currentSession = null;

        };

    });

}());
