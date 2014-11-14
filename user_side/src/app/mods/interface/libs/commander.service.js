(function () {
    "use strict";

    define([], function () {

        return function (q, http, mergeGroup) {
            var self = this;

            self.operation = {
                status: false
            };

            self.merge = mergeGroup;

            /**
             * Initializes the operation UI
             * @param  {String} name operation name
             */
            self.init_operation = function (name, net_list, session_id) {
                self.operation.name = name;
                self.operation.status = true;
                self.selected = {};

                if ( 'merge' == name ) {
                    self.merge.list = [];
                    for (var i = net_list.length - 1; i >= 0; i--) {
                        var net = net_list[i];
                        if ( 1 == net.status ) {
                            self.merge.list.push(net);
                        }
                    }
                    self.merge.toggle(session_id);
                }
            };

            /**
             * @param  {String}  name operation name
             * @return {Boolean}      if the given operation is running
             */
            self.is_operation = function (name) {
                if ( null == name ) return undefined == self.operation.name;
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

            // GROUP MERGE

            /**
             * Merges the selected networks
             */
            self.apply_merge = function() {
                console.log(1);
            };

            // GENERAL
            
            self.reset_ui = function () {
                self.operation = {
                    status: false
                };
                self.merge.reset_service();
            };

        };

    });

}());
