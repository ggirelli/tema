(function () {
    "use strict";

    define([], function () {

        return function (q, http) {
            var self = this;

            self.operation = {
                status: false
            };

            /**
             * @param  {String} session_id
             * @return {promise} it contains as .list the network list
             */
            self.get_network_list = function (session_id) {
                var qwait = q.defer();

                http({

                    method: 'POST',
                    data: {
                        action: 'get_network_list',
                        id: session_id
                    },
                    url: 's/'

                }).
                    success(function(data) {
                        qwait.resolve(data);
                    });

                return qwait.promise;
            };

            /**
             * Initializes the operation UI
             * @param  {String} name operation name
             */
            self.init_operation = function (name) {
                self.operation.name = name;
                self.operation.status = true;
                self.selected = {};
            };

            /**
             * @param  {String}  name operation name
             * @return {Boolean}      if the given operation is running
             */
            self.is_operation = function (name) {
                return name == self.operation.name;
            };

            /**
             * Reset commander
             */
            self.abort_operation = function () {
                self.operation = {
                    status: false
                };
            };

            /**
             * Merges the selected networks
             */
            self.merge = function() {
                console.log(1);
            };

        };

    });

}());
