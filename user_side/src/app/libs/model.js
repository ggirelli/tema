(function () {
    "use strict";

    define([], function () {

        return function () {
            var self = this;
            
            /*
             * 0: wait for choice
             * 1: loading old session
             */
            self.homeChoice = 0;

            self.session_id = null;
           	self.currentSession = null;


        };

    });

}());
