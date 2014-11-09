(function () {
    "use strict";

    define([], function () {

        return function () {
        	var self = this;

        	self.files = [];
			
        	self.addFile = function() {
        		self.files.push({
                    id: self.files.length
                });
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