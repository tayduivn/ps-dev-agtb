/**
 SugarAuth
 * @ignore
 */
(function(app) {
    /**
     * @class SugarAuth
     * @singleton
     * SugarAuth provides the ability to login and authentication status
     */

    app.augment('utils', (function() {

        var instance;

        /**
         * init
         * @private
         * @param args
         */
        function init() {

            instance = new Utils();
            return instance
        }

        function Utils() {

            return {
                /**
                 * checks if currently authenticated
                 *
                 * @return {Boolean} true if auth, false otherwise
                 */
                format_number: function(){
                    return 1;
                }
            };
        }

        return instance || init();
    }()))
}(SUGAR.App));