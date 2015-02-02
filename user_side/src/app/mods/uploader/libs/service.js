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

            self.dropzone = [];
			
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
             * @param  {Integer} time ms after which wrong files are removed
             */
        	self.check_list = function (time) {
                if ( 0 == self.files.length ) {
                    alert('No files to check.');
                    stop();
                }
                
                var i = 0;
        		while ( i < self.files.length ) {
                    if ( !self.check_single_file(self.files[i].data[0].name) ) {
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
             * Checks a single file extension
             * @param  {String} fname file name
             * @return {Boolean}       False if any error occurred
             */
            self.check_single_file = function (fname) {
                var ext = fname.split('.');
                ext = ext[ext.length - 1];
                if ( 'graphml' != ext ) {
                    console.log(3);
                    return false;
                } else {
                    console.log(4);
                    return true;
                }
            }

            /**
             * Begins upload
             */
        	self.upload = function (session_id) {
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

            /**
             * Uploads a single file
             * @param  {String} session_id
             * @param  {Integer} id         file id
             */
            self.single_upload = function (session_id, id) {
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
                    headers: {
                        'Content-Type': undefined
                    },
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
            self.abort = function () {
                self.files = [];
                self.files_rm = [];
                self.files_up = [];

                self.uploading = false;
                self.progress = 0;
            };

            /**
             * Ends uploading process, reset uploader controller and redirects to the interface
             */
            self.end = function (session_id) {
                // Reset queue and uploader status
                self.abort();

                // Redirect to the interface
                document.location.hash = '#/interface/' + session_id
            };

            /** DROPZONE FUNCTIONS */

            self.init_dropzone = function (session_id) {
                self.myDropzone = new Dropzone("div#dropzone", {
                    url: "s/",
                    method: 'post',
                    uploadMultiple: true,
                    parallelUploads: 5,
                    paramName: 'files',
                    acceptedFiles: '.graphml',
                    maxFilesize: 500,
                    clickable: true,
                    init: function() {
                        // Here goes any event listener
                        this.on('sendingmultiple', function(file, xhr, formData) {
                            formData.append('id', session_id)
                            formData.append('action', 'upload_drag_network');
                        });
                        this.on('successmultiple', function(file, data) {
                            console.log(file);
                            console.log(data);
                            console.log(JSON.parse(data));
                        });
                    },
                    dictDefaultMessage: "Drop your <code>.graphml</code> files here to upload them.",
                    dictFallbackMessage: "Your browser does not support drag&drop upload. Please use the <a href=''>basic uploader</a> to upload your <code>.graphml</code> files.",
                    dictFallbackText: "Please use the fallback form below to upload your files like in the olden days.",
                    dictInvalidFileType: "You can't upload files of this type.",
                    //dictFileTooBig: "",
                    //dictResponseError: "",
                    //dictCancelUpload: "",
                    //dictCancelUploadConfirmation: "",
                    //dictRemoveFile: "",
                    //dictMaxFilesExceeded: ""
                });
            };

        };

    });

}());
