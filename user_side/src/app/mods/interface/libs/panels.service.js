(function () {
    "use strict";

    define([], function () {

        return function () {
            var self = this;
        	
            /**
             * Visualization status of every panel
             * @type {Object}
             */
            self.show = {
        		graph_list: false,
        		operations: false,
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
                if( self.show.graph_list || self.show.operations || self.show.layout || self.show.style || self.show.select || self.show.settings ) return(true);
                return(false);
            };

            /**
             * Which panel is open?
             * @return {String} The open panel name
             * @return {Boolean} FALSE if no panel is open
             */
            self.whoseOpen = function() {
                if( self.show.graph_list ) return('graph_list');
                if( self.show.operations ) return('operations');
                if( self.show.layout ) return('layout');
                if( self.show.style ) return('style');
                if( self.show.select ) return('select');
                if( self.show.settings ) return('settings');
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
            };
        };

    });

}());
