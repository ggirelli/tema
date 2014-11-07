(function () {
    "use strict";

    define([], function () {

        return function (scope, model, http) {

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
	                        console.log(data);
	                    });
	        	},

		       	loading: function () {
	        		scope.m.homeChoice = 1;
	        	},

	        	isLoading: function() {
	        		return(scope.m.homeChoice == 1);
	        	},

	        	load: function(id) {
	        		console.log('load: ' + id);
	                http({

	                    method: 'POST',
	                    data: {
	                        action: 'load_session',
	                        id: scope.m.tmpID
	                    },
	                    url: 's/'

	                })
	                    .success(function (data) {
	                        console.log(data);
	                        scope.homepage.waiting();
	                    });
	        	}

	        }

        };

    });

}());