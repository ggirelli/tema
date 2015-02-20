(function () {
    "use strict";

    define([], function () {

        return function () {
            var self = this;

            /**
             * Check if the provided string is a possible password
             *     - at least one lowercase letter
             *     - at least one uppercase letter
             *     - at least one digit
             *     - at least one eight characters
             * @param  {String} pwd password candidate
             * @return {Boolean}
             */
            self.password = function(pwd) {
                if (null === pwd) return(false);

                var check = {
                    lower: /^.*[a-z].*$/,
                    upper: /^.*[A-Z].*$/,
                    digit: /^.*[0-9].*$/
                };

                // Check lowercase
                if (null === pwd.match(check.lower)) return(false);

                // Check uppercase
                if (null === pwd.match(check.upper)) return(false);

                // Check digits
                if (null === pwd.match(check.digit)) return(false);

                // Check length
                if (8 > pwd.length) return(false);

                return(true);
            };

            /**
             * Check if the provided string is a possible email
             * @param  {String} email email candidate
             * @return {Boolean}
             */
            self.email = function(email) {
                if (null === email) return(false);

                if (null === email.toUpperCase().match(/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/)) return(false);
                
                return(true);
            };


            /**
             * Check if the provided string is a possible username
             * @param  {String} usr user candidate
             * @return {Boolean}
             */
            self.user = function(usr) {
                if (null === usr) return(false);

                if (4 > usr.length) return(false);

                return(true);
            }

        };

    });

}());
