(function () {
    "use strict";

    define([], function () {

        return function (rootScope) {
            var self = this;
        	
            /**
             * Visualization status of every panel
             * @type {Object}
             */
            self.show = {
        		graph_list: false,
                attributes: false,
        		operations: false,
                filter: false,
                navigate: false,
        		layout: false,
        		style: false,
        		settings: false
        	};

            /**
             * Accordion index
             * @type {Object}
             */
            self.accordion = {
                settings: 0
            };

            /**
             * Opens/closes a certain panel
             * @param  {String} panel panel name
             * @return {null}
             */
            self.toggle = function (panel) {
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
            self.isOpen = function () {
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
            self.whoseOpen = function () {
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
            self.closeAll = function () {
                self.show = {
                    graph_list: false,
                    operations: false,
                    layout: false,
                    style: false,
                    select: false,
                    settings: false
                };
                rootScope.$broadcast('reset-panels');
            };

            /**
             * Toggles a specific accordion
             * @param  {String} panel
             * @param  {int} value accordion index
             */
            self.toggleAccordion = function (panel, value) {
                if ( self.isAccordion(panel, value) ) {
                    self.setAccordion(panel, 0);
                } else {
                    self.setAccordion(panel, value);
                }
            };

            /**
             * Opens a specific accordion
             * @param {String} panel 
             * @param {int} value accordion index
             */
            self.setAccordion = function(panel, value) {
                self.accordion[panel] = value;
            };

            self.isAccordion = function (panel, value) {
                return( value == self.accordion[panel] );
            };
        };

    });

}());
