/**
 * Keychain store manager.
 *
 * @class Core.Keychain
 * @singleton
 * @alias SUGAR.App.keychain
 */
(function(app) {
    var serviceName = "SugarCRM",
        emptyFn = function() {};
        
    var _keychain = {
        /**
         * Initializes the keychain.
         */
        init: function() {
            this.keychainPlugin = new Keychain();
        },
        /**
         * Returns the auth token of the current user.
         *
         * This method simply reads the global AUTH_TOKEN
         * that was set when the native application was launched.
         *
         * @return {String} authentication token for the current user.
         */
        get: function() {
            return app.AUTH_ACCESS_TOKEN;
        },

        /**
         * Puts an item into the keychain.
         * @param {String} key Item key.
         * @param {String} value Item to put.
         */
        set: function(key, value) {
            this.keychainPlugin.setForKey(key, value, serviceName, emptyFn, emptyFn);
        },

        /**
         * Deletes an item from the keychain.
         * @param {String} key Item key.
         */
        cut: function(key) {
            this.keychainPlugin.removeForKey(key, serviceName, emptyFn, emptyFn);
        }
    };

    app.augment("keychain", _keychain);

})(SUGAR.App);
