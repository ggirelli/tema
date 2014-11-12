(function () {
    "use strict";

    define([], function () {

        return function (q, http) {
            var self = this;

            self.info = {
                sif: undefined,
                sif_keys: [],
                sif_sample_col: null,
                node_thr: 1000,
                goa: undefined,
                gob: undefined,
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
                            file: fTmp,
                            action: 'upload_sif'
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
                        action: 'get_sif',
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
             * If the given session has a SIF
             * @param  {String}  session_id
             * @return {Boolean}            if a SIF is present in the given session
             */
            self.is_sif = function (session_id) {
                var qwait = q.defer();

                http({

                    method: 'POST',
                    data: {
                        action: 'get_sif',
                        id: session_id
                    },
                    url: 's/'

                }).
                    error(function (data) {
                        qwait.resolve(false);
                    }).
                    success(function (data) {
                        qwait.resolve(true);
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
                $('#goa-input').unbind('change');
                $('#goa-input').on('change', function (e) {
                    e.preventDefault();
                    var fTmp = $(this)[0].files[0];
                    
                    http({

                        method: 'POST',
                        data: {
                            id: session_id,
                            file: fTmp,
                            action: 'upload_goa'
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
                            console.log(data)
                        });

                });

                $('#goa-input').trigger('click');
            };

            /**
             * Retrieves the GOA file-name
             * @param  {String} session_id
             * @return {promise}            contains goa as data
             */
            self.get_goa = function (session_id) {
                var qwait = q.defer();

                http({

                    method: 'POST',
                    data: {
                        action: 'get_goa',
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
             * If the GOA file-name is available (user-side)
             * @return {Boolean} if the GOA file-name is loaded in angularJS
             */
            self.is_goa_ready = function () {
                if ( undefined != self.info.goa ) {
                    return true;
                }
                return false;
            };

            /**
             * Uploads the GOB
             * @param  {String} session_id
             */
            self.upload_gob = function (session_id) {
                $('#gob-input').unbind('change');
                $('#gob-input').on('change', function (e) {
                    e.preventDefault();
                    var fTmp = $(this)[0].files[0];
                    
                    http({

                        method: 'POST',
                        data: {
                            id: session_id,
                            file: fTmp,
                            action: 'upload_goa'
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
                            console.log(data)
                        });

                });

                $('#gob-input').trigger('click');
            };

            /**
             * Retrieves the GOB file-name
             * @param  {String} session_id
             * @return {promise}            contains gob as data
             */
            self.get_gob = function (session_id) {
                var qwait = q.defer();

                http({

                    method: 'POST',
                    data: {
                        action: 'get_gob',
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
             * If the GOB file-name is available (user-side)
             * @return {Boolean} if the GOB file-name is loaded in angularJS
             */
            self.is_gob_ready = function () {
                if ( undefined != self.info.gob ) {
                    return true;
                }
                return false;
            };

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
                        data['node_thr'] = parseInt(data['node_thr']);
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
                        console.log(data);
                        qwait.resolve(data);
                    });

                return qwait.promise;
            };

        };

    });

}());
