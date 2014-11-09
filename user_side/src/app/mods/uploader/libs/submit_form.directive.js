(function () {
    "use strict";

    define([], function () {

        return function (scope, e, attrs) {
            $(e).click(function () {
                scope.uploader.upload(scope.m.session_id);
                scope.$apply();
            });
        };

    });

}());
