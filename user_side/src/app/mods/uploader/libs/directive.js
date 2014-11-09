(function () {
    "use strict";

    define([], function () {

        return function (scope, e, attrs) {
            $(e).trigger('click');
            
            // Bind input value and module
            e.bind('change', function () {
                if ( !scope.uploader.inQueue(e[0].files[0].name) ) {
                    scope.uploader.files[parseInt(attrs.uploaderAutoclick) - 1].data = e[0].files;
                    scope.$apply();
                } else {
                    scope.uploader.rmFile(parseInt(attrs.uploaderAutoclick));
                    scope.$apply();
                }
            });
        };

    });

}());
