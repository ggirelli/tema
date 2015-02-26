(function () {
    "use strict";

    define([], function () {

        return function (scope, model, routeParams, uploader) {

        	scope.m = model;
        	scope.m.session_id = routeParams.id;
        	scope.m.uploader_type = "0";

        	scope.uploader = uploader;

        	scope.toggleUploader = function () {
                scope.m.uploading = !scope.m.uploading;
            };

        };

    });

}());
