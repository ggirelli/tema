(function () {
    "use strict";

    define([], function () {

        return function (http, q, timeout) {
        	var self = this;

            self.uploading = false;
            self.progress = 0;

            /**
             * Array with file informations: id, class and data
             * @type {Array}
             */
        	self.files = [];
            self.files_rm = [];
            self.files_up = []
			
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
                    id: new_id,
                    data: [{
                        name: 'undefined',
                    }],
                    class: 'warning'
                });
        	};

            /**
             * Remove a file from the queue
             * @param  {int} id index of the file to remove in self.files
             */
            self.rmFile = function (id) {
                self.files.splice(id, 1);
                for (var i = self.files.length - 1; i >= 0; i--) {
                    self.files[i].id = i + 1;
                };
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
                var i = 0;
        		while ( i < self.files.length ) {
                    var ext = self.files[i].data[0].name.split('.');
                    ext = ext[ext.length - 1];
                    if ( 'graphml' != ext ) {
                        self.files_rm.push(self.files[i]);
                        self.rmFile(i);
                    } else {
                        i++;
                    }
                };
                timeout(function() {
                    self.files_rm = [];
                }, time);
        	};

            /**
             * Begins upload
             */
        	self.upload = function(session_id) {
                // Impose a check on the Queue
                self.check_list(0);

                // Start uploading
                if ( 0 != self.files.length ) {
                    self.uploading = true;
                    self.files_up = self.files;
                    self.files = [];

                    // Send one file at a time
                    self.single_upload(session_id, 0);

                } else {
                    alert('You can upload the files after you select them.');
                }                    
        	};

            self.single_upload = function(session_id, id) {
                // defer the main process
                var qwait = q.defer();
                // defer the single recursions
                var qrecursive = q.defer();
                // focus on a single file
                var fTmp = self.files_up[id].data[0];

                http({

                    method: 'POST',
                    data: {
                        id: session_id,
                        file: fTmp,
                        action: 'upload_network'
                    },
                    url: 's/',
                    headers: { 'Content-Type': undefined },
                    transformRequest: function (data) {
                        var fd = new FormData();
                        angular.forEach(data, function (value, key) {
                            fd.append(key, value);
                        });
                        return fd;
                    }

                }).
                    success(function (data) {
                        if ( 1 == data['err'] || 2 == data['err'] ) {
                            // Change alert status
                            self.files_up[id].class = 'danger';
                            self.files_up[id].msg = 'ERROR: cannot perform the request.';
                        } else if ( 3 == data['err'] ) {
                            // Change alert status
                            self.files_up[id].class = 'danger';
                            self.files_up[id].msg = 'ERROR: trying to upload to non-existing session.';
                        } else if ( 4 == data['err'] ) {
                            // Change alert status
                            self.files_up[id].class = 'danger';
                            self.files_up[id].msg = 'ERROR: banned file.';
                        } else if ( 5 == data['err'] ) {
                            // Change alert status
                            self.files_up[id].class = 'danger';
                            self.files_up[id].msg = 'ERROR: wrong extension.';
                        } else if ( 6 == data['err'] ) {
                            // Change alert status
                            self.files_up[id].class = 'danger';
                            self.files_up[id].msg = 'ERROR: this file already exists, try renaming it.';
                        } else {
                            // Change alert status
                            self.files_up[id].class = 'success';
                            self.files_up[id].msg = 'File correctly uploaded';
                        }
                        // Update progress
                        self.progress = Math.round(((id + 1) / self.files_up.length) * 100)

                        // Resolve single recursion
                        qrecursive.resolve(data);
                    });

                qrecursive.promise.then(function () {
                    if ( id < self.files_up.length - 1 ) {
                        self.single_upload(session_id, id+1).then(function () {
                            qwait.resolve();
                        });
                    } else {
                        qwait.resolve();
                    }
                });

                return qwait.promise;
            }

            /**
             * Clears the queue befor aborting
             */
            self.abort = function() {
                self.files = [];
                self.files_rm = [];
                self.files_up = [];
            };

            /**
             * Ends uploading process, reset uploader controller and redirects to the interface
             */
            self.end = function(session_id) {
                // Reset uploader status
                self.uploading = false;

                // Reset queue
                self.abort();

                // Redirect to the interface
                document.location.hash = '#/interface/' + session_id
            }

        };

    });

}());
