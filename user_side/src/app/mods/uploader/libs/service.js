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

            self.rmFile = function(id) {
                self.files.splice(id, 1);
            };

        	self.check_list = function() {
        		console.log(2);
        	};

        	self.upload = function() {
        		console.log(3);
        	};

        };

    });

}());