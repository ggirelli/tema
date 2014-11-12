(function () {
    "use strict";

    define([], function () {

        return function (q, http) {
            var self = this;

            self.list = null;

            /**
             * Loads a network, after converting it, if required
             * @param {int} id network id
             * @param {String} session_id
             */
            self.convert = function (network, session_id) {
                if ( 0 == network.status ) {
                    var qwait = q.defer();

                    http({

                        method: 'POST',
                        data: {
                            action: 'convert_network',
                            id: session_id,
                            network_id: network.id
                        },
                        url: 's/'

                    }).
                        success(function (data) {
                            if ( undefined != data['status'] ) {
                                self.list[network.id].status = parseInt(data['status']);
                                self.list[network.id].data = data['data'];
                            }
                            qwait.resolve(data);
                        });

                    return qwait.promise;
                }
            };

            /**
             * @param  {Object}  network from self.list
             * @return {Boolean}         If the given network is converted
             */
            self.isConverted = function (network) {
                if ( 1 == network.status) {
                    return true;
                }
                return false;
            };

            /**
             * Applies SIF information to the network list
             * @param  {Object} sif
             * @param  {String} sample_col
             */
            self.apply_sif = function (info) {
                // Retrieve SIF
                var sif = info['sif'];

                if ( undefined != sif) {

                    // SIF columns
                    var cols = info['sif_keys'];
                    // SIF sample column
                    var sample_col = info['sif_sample_col'];

                    // Apply to each network in self.list
                    for (var i = self.list.length - 1; i >= 0; i--) {
                        var network = self.list[i];

                        // Look for the network in the SIF
                        var j = sif[sample_col].indexOf(network.name);

                        // Start building network-specific SIF_data
                        var sif_data = {}
                        if ( -1 != j ) {
                            // Retrieve SIF data
                            for (var k = cols.length - 1; k >= 0; k--) {
                                var col = cols[k];
                                sif_data[col] = sif[col][j];
                            };
                        }
                        // Assign SIF data
                        self.list[i]['sif_data'] = sif_data;

                        // Assign single-sample status
                        if ( {} == sif_data ) {
                            // multi-sample network
                            self.list[i]['single'] = false;
                        } else {
                            // single-sample network
                            self.list[i]['single'] = true;
                        }
                    };
                }
            };

        };

    });

}());
