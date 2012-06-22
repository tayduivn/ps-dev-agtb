/**
 * Keychain store manager.
 *
 * @class Nomad.Keychain
 * @singleton
 * @alias SUGAR.App.keychain
 */
(function(app) {
    var serviceName = "SugarCRM",
        emptyFn = function() {};
        
    var _keychain = {

        /**
         * Returns the auth token of the current user.
         *
         * This method simply reads the global AUTH_ACCESS_TOKEN or
         * AUTH_REFRESH_TOKEN that was set when the native application was launched.
         *
         * @param {String} key Item key.
         * @return {String} authentication token for the current user.
         */
        get: function(key) {
            return app.OAUTH[key];
        },

        /**
         * Puts an item into the keychain.
         * @param {String} key Item key.
         * @param {String} value Item to put.
         */
        set: function(key, value) {
            window.plugins.keychain.setForKey(key, value, serviceName, emptyFn, emptyFn);
        },

        /**
         * Deletes an item from the keychain.
         * @param {String} key Item key.
         */
        cut: function(key) {
            window.plugins.keychain.removeForKey(key, serviceName, emptyFn, emptyFn);
        }
    };

    app.augment("keychain", _keychain);

})(SUGAR.App);
