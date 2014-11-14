(function () {
    "use strict";

    define([], function () {

        return function (q, http, rootScope) {
            var self = this;

            self.list = [];

            // GROUP ACTIONS

            self.group = {
                status: false,
                all: false,
                doing: false,
                networks: {}
            };

            /**
             * @return {Boolean} if the group action interface is open
             */
            self.is = function () {
                return self.group.status;
            };

            /**
             * Toggles the group action interface
             * @param  {String} session_id
             */
            self.toggle = function (session_id) {
                self.group.id = session_id;
                self.group.status = !self.group.status;
                self.group.net_list = self.list;

                if ( self.group.status ) {
                    for (var i = self.list.length - 1; i >= 0; i--) {
                        self.group.networks[self.list[i].name] = false;
                    }
                } else {
                    self.group.all = false;
                }
            };

            /**
             * Un/Selects all networks
             */
            self.un_select_all = function () {
                var ks = Object.keys(self.group.networks);
                self.group.all = !self.group.all;
                for (var i = ks.length - 1; i >= 0; i--) {
                    self.group.networks[ks[i]] = self.group.all;
                }
            };

            /**
             * Updates selection and checks un/select-all button
             * @param  {String} name network name
             */
            self.check = function (name) {
                self.group.networks[name] = !self.group.networks[name];

                var all_checked = true;
                var ks = Object.keys(self.group.networks);
                for (var i = ks.length - 1; i >= 0; i--) {
                    if ( !self.group.networks[ks[i]] ) {
                        all_checked = false;
                    }
                }
                self.group.all = all_checked;
            };

            // SELECTION FILTERS
            
            self.filters = [];

            /**
             * Toggles filter select in selecting_group.filter
             */
            self.toggle_filter = function () {
                self.group.net_list = self.list;

                if ( undefined == self.group.filter ) {
                    self.group.filter = true;
                } else {
                    self.group.filter = !self.group.filter;
                }

                if ( self.group.filter ) {
                    self.group.net_attr_values = self.get_network_attrs_values(self.list);
                    self.group.net_attributes = Object.keys(self.group.net_attr_values);
                    self.filters.push({
                        combine: null,
                        attribute: '',
                        condition: '',
                        value: '',
                        object: false
                    });
                } else {
                    self.filters = [];
                }
            };

            /**
             * @param  {object} net_list
             * @return {object} networks attributes with values
             */
            self.get_network_attrs_values = function (net_list) {
                // Output object
                var o = {};

                // For each network
                for (var i = net_list.length - 1; i >= 0; i--) {
                    var net = net_list[i];

                    // For each attribute
                    var ks = Object.keys(net);
                    for (var l = ks.length - 1; l >= 0; l--) {
                        var k = ks[l];
                        // is it an object
                        if ( 'object' != typeof(net[k]) ) {
                            if ( -1 == Object.keys(o).indexOf(k) ) {
                                o[k] = [net[k]];
                            } else {
                                if ( -1 == o[k].indexOf(net[k]) ) {
                                    o[k].push(net[k]);
                                } else {
                                }
                            }
                        } else {
                            var js = Object.keys(net[k]);
                            if ( 0 != js.length) {
                                for (var m = js.length - 1; m >= 0; m--) {
                                    var j = js[m];
                                    var kj = k + ',' + j;
                                    if ( -1 == Object.keys(o).indexOf(kj) ) {
                                        o[kj] = [net[k][j]];
                                    } else {
                                        if ( -1 == o[kj].indexOf(net[k][j]) ) {
                                            o[kj].push(net[k][j]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                
                // Clean it
                var ks = Object.keys(o);
                for (var i = ks.length - 1; i >= 0; i--) {
                    var k = ks[i];
                    if ( -1 != k.indexOf('$') || 2 > o[k].length ) {
                        delete o[k];
                    }
                };

                return o;
            };

            /**
             * Adds a filter
             */
            self.add_filter = function () {
                self.filters.push({
                    combine: '',
                    attribute: '',
                    condition: '',
                    value: '',
                    object: false
                });
            };

            /**
             * @param  {integer} i index of a network in the list
             * @return {object}   values of the currently selected attribute
             */
            self.get_net_attr_values = function (i) {
                var out = self.group.net_attr_values[self.filters[i].attribute];
                if ( undefined != out ) {
                    // If an array, signal it
                    self.filters[i].object = Array.isArray(out[0]);
                    if ( self.filters[i].object ) {
                        var new_out = [];
                        for (var i = out.length - 1; i >= 0; i--) {
                            for (var j = out[i].length - 1; j >= 0; j--) {
                                var el = out[i][j];
                                if ( -1 == new_out.indexOf(el) ) {
                                    new_out.push(el);
                                }
                           }
                        }
                        return new_out;
                    } else {
                        return out;
                    }
                }
            };

            /**
             * @return {Boolean} If the current filters are complete
             */
            self.check_filters = function () {
                var checked = true
                for (var i = self.filters.length - 1; i >= 0; i--) {
                    var filter = self.filters[i];
                    if ( '' == filter.combine ) checked = false;
                    if ( '' == filter.attribute ) checked = false;
                    if ( '' == filter.condition ) checked = false;
                    if ( '' == filter.value ) checked = false;
                }
                return checked;
            };

            /**
             * Filters the current network list
             */
            self.apply_filters = function () {
                var net_list = self.list;
                var net_list_clean = [];
                if ( self.check_filters() ) {
                    for (var i = 0; i < net_list.length; i++) {
                        var net = net_list[i];
                        var res = false;
                        for (var j = 0; j < self.filters.length; j++ ) {
                            var filter = self.filters[j];
                            
                            if ( -1 != filter.attribute.indexOf(',') ) {
                                var a_attr = filter.attribute.split(',');
                                var k = net[a_attr[0]][a_attr[1]];
                            } else {
                                var k = net[filter.attribute];
                            }
                            var v = filter.value;
                            var tmpRes = false;


                            if ( 'e' == filter.condition ) {
                                tmpRes = ( '' + k == '' + v );
                            } else if ( 'ne' == filter.condition ) {
                                tmpRes = ( '' + k != '' + v );
                            } else if ( 'lt' == filter.condition ) {
                                tmpRes = ( parseInt(k) < parseInt(v) );
                            } else if ( 'le' == filter.condition ) {
                                tmpRes = ( parseInt(k) <= parseInt(v) );
                            } else if ( 'gt' == filter.condition ) {
                                tmpRes = ( parseInt(k) > parseInt(v) );
                            } else if ( 'ge' == filter.condition ) {
                                tmpRes = ( parseInt(k) >= parseInt(v) );
                            } else if ( 'c' == filter.condition ) {
                                tmpRes = ( -1 != k.indexOf(v) );
                            }

                            if ( null == filter.combine ) {
                                res = tmpRes;
                            } else if ( 'AND' == filter.combine ) {
                                res = res && tmpRes;
                            } else if ( 'OR' == filter.combine ) {
                                res = res || tmpRes;
                            }

                        }

                        if ( res ) {
                            net_list_clean.push(net);
                        }

                    }
                    self.group.net_list = net_list_clean;

                    var tmpNetworks = {}
                    for (var i = net_list_clean.length - 1; i >= 0; i--) {
                        tmpNetworks[net_list_clean[i].name] = false;
                    }
                    self.group.networks = tmpNetworks;
                }
            };

            /**
             * @param  {String} attr name
             * @return {String}      the attribute name if it is not an array attribute
             * @return {array}      the attribute name and parent name if it is an array attribute
             */
            self.extract_attr_name = function (attr) {
                if ( -1 != attr.indexOf(',') ) {
                    var a_attr = attr.split(',');
                    return a_attr[a_attr.length - 1];
                } else {
                    return attr;
                }
            };   

        };

    });

}());
