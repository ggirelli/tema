(function () {
    "use strict";

    define([], function () {

        return function (q, http) {
            var self = this;

            self.get_network_list = function(id) {
                var qwait = q.defer();

                http({

                    method: 'POST',
                    data: {
                        action: 'get_network_list',
                        id: id
                    },
                    url: 's/'

                }).
                    success(function(data) {
                        console.log(data);
                        qwait.resolve(data);
                    });

                return q.promise;
            }

        };

    });

}());