(function () {
    "use strict";

    define([], function () {

        return function (timeout) {
        	var self = this;

            /**
             * Array with file informations: id, class and data
             * @type {Array}
             */
        	self.files = [];
			
            /**
             * Add a file to the queue
             */
        	self.addFile = function () {
        		self.files.push({
                    id: self.files.length,
                    class: 'info'
                });
        	};

            /**
             * Remove a file from the queue
             * @param  {int} id index of the file to remove in self.files
             */
            self.rmFile = function (id) {
                self.files.splice(id, 1);
            };

            /**
             * Checks files in the queue and removes error
             */
        	self.check_list = function() {
        		for (var i = self.files.length - 1; i >= 0; i--) {
                    var ext = self.files[i].data[0].name.split('.');
                    ext = ext[ext.length - 1];
                    if ( 'graphml' != ext ) {
                        self.files[i].class = 'danger';
                        timeout(function () {
                            self.rmFile(i - 1);
                        }, 3000);
                    }
                };
        	};

            /**
             * Begins upload
             */
        	self.upload = function() {
        		console.log(3);
        	};

            /**
             * Clears the queue befor aborting
             */
            self.abort = function() {
                self.files = [];
            };

        };

    });

}());