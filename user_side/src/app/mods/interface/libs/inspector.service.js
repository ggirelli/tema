(function () {
    "use strict";

    define([], function () {

        return function () {
            var self = this;
        	
            /**
             * Visualization status
             * @type {Boolean}
             */
            self.show = false;

            /**
             * Opens/closes the inspector
             * @return {null}
             */
            self.toggle = function() {
                self.show = !self.show;
            }
        };

    });

}());