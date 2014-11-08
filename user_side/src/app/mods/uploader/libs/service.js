(function () {
    "use strict";

    define([], function () {

        return function () {
        	var self = this;

        	self.addFile = function() {
        		alert(1);
        	};

        	self.check_list = function() {
        		alert(2);
        	};

        	self.upload = function() {
        		alert(3);
        	};

        };

    });

}());