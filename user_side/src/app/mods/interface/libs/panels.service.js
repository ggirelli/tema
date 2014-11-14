(function () {
    "use strict";

    define([], function () {

        return function (rootScope, timeout) {
            var self = this;
        	
            /**
             * Visualization status of every panel
             * @type {Object}
             */
            self.show = {
        		graph_list: false,
        		operations: false,
                attributes: false,
        		layout: false,
        		style: false,
        		select: false,
        		settings: false
        	};

            /**
             * Opens/closes a certain panel
             * @param  {String} panel panel name
             * @return {null}
             */
            self.toggle = function(panel) {
                if ( self.isOpen() ) {
                    if ( panel != self.whoseOpen() ) {
                        self.closeAll();
                    }
                }
                self.show[panel] = !self.show[panel];
            };

            /**
             * Checks whether a panel is open
             * @return {Boolean}
             */
            self.isOpen = function() {
                var ks = Object.keys(self.show);
                for (var i = ks.length - 1; i >= 0; i--) {
                    if ( self.show[ks[i]] ) {
                        return(true);
                    }
                }
                return(false);
            };

            /**
             * Which panel is open?
             * @return {String} The open panel name
             * @return {Boolean} FALSE if no panel is open
             */
            self.whoseOpen = function() {
                var ks = Object.keys(self.show);
                for (var i = ks.length - 1; i >= 0; i--) {
                    if ( self.show[ks[i]] ) {
                        return(ks[i]);
                    }
                }
                return(false);
            };

            /**
             * Closes every panel
             * @return {[type]} [description]
             */
            self.closeAll = function() {
                self.show = {
                    graph_list: false,
                    operations: false,
                    layout: false,
                    style: false,
                    select: false,
                    settings: false
                };
                timeout(function () {
                    rootScope.$broadcast('reset-panels');
                }, 900);
            };
        };

    });

}());
