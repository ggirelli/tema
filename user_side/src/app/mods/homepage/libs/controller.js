(function () {
    "use strict";

    define([], function () {

        return function (scope, model, http, timeout) {

        	scope.m = model;
        	
        	scope.homepage = {

	        	waiting: function () {
	        		scope.m.homeChoice = 0;
	        	},

	        	isWaiting: function () {
	        		return(scope.m.homeChoice == 0);
	        	},

	        	new: function() {
	                http({

	                    method: 'POST',
	                    data: {
	                        action: 'new_session'
	                    },
	                    url: 's/'

	                })
	                    .success(function (data) {
	                        if(0 == data['err']) {
	                        	document.location.hash = data['hash'];
	                        } else {
	                        	console.log(data);
	                        }
	                    });
	        	},

		       	loading: function () {
	        		scope.m.homeChoice = 1;
	        	},

	        	isPreLoading: function() {
	        		return(scope.m.homeChoice == 1);
	        	},

	        	load: function(id) {
	        		scope.m.homeChoice = 2;

	                http({

	                    method: 'POST',
	                    data: {
	                        action: 'load_session',
	                        id: scope.m.tmpID
	                    },
	                    url: 's/'

	                })
	                    .success(function (data) {
	                        if(0 == data['err']) {
	                        	document.location.hash = data['hash'];
	                        	scope.m.tmpID = undefined;
	                        	scope.homepage.waiting();
	                        } else {
	                        	scope.m.tmpErr = 3;
	                        	console.log(data);
	                        	timeout(function() {
	                        		scope.m.tmpErr = undefined;
	                        		scope.m.tmpID = undefined;
	                        		scope.homepage.waiting();
	                        	}, 3000);
	                        }
	                    });
	        	},

	        	isLoading: function() {
	        		return(scope.m.homeChoice == 2);
	        	}

	        }

        };

    });

}());