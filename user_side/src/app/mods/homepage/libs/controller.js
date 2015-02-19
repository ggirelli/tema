(function () {
    "use strict";

    define([], function () {

        return function (scope, model, http, timeout) {

        	scope.m = model;
        	
        	scope.up = {

    			setSigning: function(val) {
    				scope.m.homepage.up = val;
    			},

    			isSigning: function() {
    				return(scope.m.homepage.up);
    			},

    			sign: function() {

    			}

    		};

        	scope.in = {

    			setSigning: function(val) {
    				scope.m.homepage.in = val;
    			},

    			isSigning: function() {
    				return(scope.m.homepage.in);
    			},

    			sign: function() {

    			}

    		};

    		scope.load = {

    			setLoading: function(val) {
    				scope.m.homepage.load = val;
    			},

    			isLoading: function() {
    				return(scope.m.homepage.load);
    			},

    			load: function() {

    			}

    		};

        	scope.homepage_bak = {

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
	                        action: 'session_new'
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
	                        action: 'session_load',
	                        id: scope.m.tmpID
	                    },
	                    url: 's/'

	                })
	                    .success(function (data) {
	                    	console.log(data);
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

	        };

        };

    });

}());
