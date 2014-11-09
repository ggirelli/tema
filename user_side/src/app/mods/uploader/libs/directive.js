(function () {
    "use strict";

    define([], function () {

        return function (scope, e, attrs) {
            $(e).trigger('click');

            // Bind input value and module
            e.bind('change', function () {
                scope.uploader.files[attrs.fileId].data = e[0].files;
                scope.$apply();
            });
        };

    });

}());