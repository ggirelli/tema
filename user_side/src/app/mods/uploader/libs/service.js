(function () {
    "use strict";

    define([], function () {

        return function (q, timeout) {
        	var self = this;

            self.uploading = false;

            /**
             * Array with file informations: id, class and data
             * @type {Array}
             */
        	self.files = [];
            self.files.rm = []
			
            /**
             * Add a file to the queue
             */
        	self.addFile = function () {
                var new_id;
                if ( 0 == self.files.length ) {
                    new_id = 1;
                } else {
                     new_id = self.files[self.files.length - 1].id + 1;
                }
        		self.files.push({
                    id: new_id
                });
        	};

            /**
             * Remove a file from the queue
             * @param  {int} id index of the file to remove in self.files
             */
            self.rmFile = function (id) {
                self.files.splice(id - 1, 1);
            };

            /**
             * Checks whether a file is already in the queue
             * @param  {String} name file name
             * @return {boolean}
             */
            self.inQueue = function (name) {
                for (var i = self.files.length - 1; i >= 0; i--) {
                    if ( undefined != self.files[i].data ) {
                        if ( name == self.files[i].data[0].name ) {
                            return true;
                        }
                    }
                };
                return false;
            }

            /**
             * Checks files in the queue and removes error
             */
        	self.check_list = function(time) {
        		for (var i = self.files.length - 1; i >= 0; i--) {
                    var ext = self.files[i].data[0].name.split('.');
                    ext = ext[ext.length - 1];
                    if ( 'graphml' != ext ) {
                        self.files.rm.push(self.files[i]);
                        self.rmFile(i - 1);
                    }
                };
                timeout(function() {
                    self.files.rm = [];
                }, time);
        	};

            /**
             * Begins upload
             */
        	self.upload = function() {
                // Impose a check on the Queue
                self.check_list(0);

                // Start uploading
                if ( 0 != self.files.length ) {
                    self.uploading = true;
                } else {
                    alert('You can upload the files after you select them.');
                }                    
        	};

            /**
             * Clears the queue befor aborting
             */
            self.abort = function() {
                self.files = [];
                self.files.rm = [];
            };

        };

    });

}());
