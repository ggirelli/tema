(function () {
    "use strict";

    define([], function () {

        return function (q, http, rootScope) {
            var self = this;

            self.info = {
                sif: undefined,
                sif_keys: [],
                sif_sample_col: null,
                node_thr: 1000,
                goa: false,
                gob: false,
                go_status: false
            };

            /**
             * Uploads the SIF
             * @param  {String} session_id
             */
            self.upload_sif = function (session_id) {
                $('#sif-input').unbind('change');
                $('#sif-input').on('change', function (e) {
                    e.preventDefault();
                    var fTmp = $(this)[0].files[0];
                    
                    http({

                        method: 'POST',
                        data: {
                            id: session_id,
                            type: 'sif',
                            file: fTmp,
                            action: 'upload_setting_file'
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
                            self.get_sif(session_id).then(function (data) {
                                if ( 0 == data['err']) {
                                    self.info.sif = data.sif;
                                    if ( self.is_sif_ready() ) {
                                        self.info.sif_keys = Object.keys(self.info.sif);
                                    }
                                }
                            });
                        });

                });

                $('#sif-input').trigger('click');
            };

            /**
             * Retrieves the SIF
             * @param  {String} session_id
             * @return {promise}            contains sif as data
             */
            self.get_sif = function (session_id) {
                var qwait = q.defer();

                http({

                    method: 'POST',
                    data: {
                        action: 'get_setting_file',
                        type: 'sif',
                        id: session_id
                    },
                    url: 's/'

                }).
                    success(function (data) {
                        qwait.resolve(data);
                    });

                return qwait.promise;
            };

            /**
             * If the given session has a setting file
             * @param  {String}  session_id
             * @param {String} type one of the setting file labels
             * @return {Boolean}            if a setting file is present in the given session
             */
            self.is_file = function (session_id, type) {
                var qwait = q.defer();

                http({

                    method: 'POST',
                    data: {
                        action: 'get_setting_file',
                        type: type,
                        id: session_id
                    },
                    url: 's/'

                }).
                    error(function (data) {
                        qwait.resolve({res:false, data:data});
                    }).
                    success(function (data) {
                        qwait.resolve({res:true, data:data});
                    });

                return qwait.promise;
            };

            /**
             * If the SIF is available (user-side)
             * @return {Boolean} if the SIF is loaded in angularJS
             */
            self.is_sif_ready = function () {
                if ( undefined != self.info.sif ) {
                    return true;
                }
                return false;
            };

            self.is_sif_sample_col = function (col) {
                return self.info.sif_sample_col == col;
            };

            /**
             * Uploads the GOA
             * @param  {String} session_id
             */
            self.upload_goa = function (session_id) {
                self.info.goa = false;
                $('#goa-input').unbind('change');
                $('#goa-input').on('change', function (e) {
                    e.preventDefault();
                    var fTmp = $(this)[0].files[0];
                    
                    http({

                        method: 'POST',
                        data: {
                            id: session_id,
                            type: 'goa',
                            file: fTmp,
                            action: 'upload_setting_file'
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
                            if ( 0 == data.err ) self.info.goa = true;
                        });

                });

                $('#goa-input').trigger('click');
            };

            /**
             * Uploads the GOB
             * @param  {String} session_id
             */
            self.upload_gob = function (session_id) {
                self.info.gob = false;
                $('#gob-input').unbind('change');
                $('#gob-input').on('change', function (e) {
                    e.preventDefault();
                    var fTmp = $(this)[0].files[0];
                    
                    http({

                        method: 'POST',
                        data: {
                            id: session_id,
                            type: 'gob',
                            file: fTmp,
                            action: 'upload_setting_file'
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
                            if ( 0 == data.err ) self.info.gob = true;
                        });

                });

                $('#gob-input').trigger('click');
            };

            /**
             * Checks for GO files
             * @param  {type}  type 'a', 'b' or undefined
             * @return {Boolean}      if the specified file, or both if undefined, is present
             */
            self.is_go_ready = function (type) {
                if ( undefined == type ) return(self.info.goa && self.info.gob)
                if ( 'a' == type ) return(self.info.goa);
                if ( 'b' == type ) return(self.info.gob);
                return false;
            };

            /**
             * Triggers the GO mapper
             * @param  {String} session_id
             */
            self.apply_go_mapper = function (session_id) {
                var qwait = q.defer();
                self.info.go_status = false;

                http({

                    method: 'POST',
                    data: {
                        id: session_id,
                        action: 'map_gos'
                    },
                    url: 's/'

                }).
                    success(function (data) {
                        self.is_go_mapped(session_id);
                        qwait.resolve(data);
                    });


                return qwait.promise;
            };

            self.is_go_mapped = function (session_id) {
                var qwait = q.defer();
                self.info.go_status = false;

                http({

                    method: 'POST',
                    data: {
                        action: 'get_setting_file',
                        id: session_id,
                        type: 'go_mgmt'
                    },
                    url: 's/'

                }).
                    success(function (data) {
                        if ( 0 == data.err ) {
                            self.info.go_status = true;
                        }
                        qwait.resolve(data);
                    });

                return qwait.promise;
            }

            /**
             * Loads the settings in angularJS
             * @param  {String} session_id
             */
            self._read = function (session_id) {
                var qwait = q.defer();

                http({

                    method: 'POST',
                    data: {
                        action: 'get_settings',
                        id: session_id
                    },
                    url: 's/'

                }).
                    success(function (data) {
                        self.info.sif_sample_col = data['sif_sample_col'];
                        self.info.node_thr = parseInt(data['node_thr']);

                        qwait.resolve(data);
                    });

                return qwait.promise;
            };

            /**
             * Applies settings changes to the session
             * @param  {String} session_id
             */
            self._apply = function (session_id) {
                var qwait = q.defer();

                http({

                    method: 'POST',
                    data: {
                        action: 'set_settings',
                        id: session_id,
                        data: self.info
                    },
                    url: 's/'

                }).
                    success(function (data) {
                        // Trigger networks.apply_sif
                        rootScope.$broadcast('apply_sif', self.info);

                        qwait.resolve(data);
                    });

                return qwait.promise;
            };

            /**
             * Sends event trigger through the scopes.
             */
            self.trigger_apply_sif = function () {
                if ( self.is_sif_ready() ) {
                    rootScope.$broadcast('apply_sif', self.info);
                }
            };

        };

    });

}());
