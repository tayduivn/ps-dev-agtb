(function(app) {
    /**
     * Authentication manager provides the ability to login/logout and check for authentication status.
     *
     * @class Core.SugarAuth
     * @singleton
     * @alias SUGAR.App.sugarAuth
     */

    app.augment('sugarAuth', (function() {

        var instance;
        var api;
        var _userLoginCallbacks;
        var _userLogoutCallbacks;

        /**
         * init
         * @private
         * @param args
         */
        function init() {

            instance = new AuthManager();
            return instance
        }

        function AuthManager() {
            /**
             * handle login success
             * @private
             * @param {Object} data contains token for current session
             */
            function handleLoginSuccess(data) {
                if (_userLoginCallbacks && _userLoginCallbacks.success) {
                    _userLoginCallbacks.success(data);
                }
            }

            /**
             * handle login error function
             * @private
             * @param {Object} data jquery ajax object from failure with codes
             */
            function handleLoginFailure(data){
                if (_userLoginCallbacks && _userLoginCallbacks.error) {
                    _userLoginCallbacks.error(data);
                }
            }

            /**
             * handles logout success
             * @private
             * @param {Object} handles logout success currently data is null
             */
            function handleLogoutSuccess(data) {
                if (_userLogoutCallbacks && _userLogoutCallbacks.success) {
                    _userLogoutCallbacks.success(data);
                }
            }

            /**
             * handle logout error function
             * @private
             * @param {Object} data jquery ajax object from failure with codes
             */
            function handleLogoutFailure(data){
                if (_userLogoutCallbacks && _userLogoutCallbacks.error) {
                    _userLogoutCallbacks.error(data);
                }
            }


            return {
                /**
                 * checks if currently authenticated
                 *
                 * @return {Boolean} true if auth, false otherwise
                 */
                isAuthenticated: function(){
                    return app.api.isAuthenticated();
                },

                /**
                 * logs users in, on success the user token will be given as the only arg to the succcess callback
                 *
                 * @param  {Object} args arguments with args.username and password args.options contains client info in a hash
                 * @param  {Object} {success: function(data){}, error: function(data){}}
                 * @return
                 */
                login: function(args, callbacks){
                    if(callbacks){
                        _userLoginCallbacks = callbacks;
                    }
                    var options = args.options || {};
                    var myCallbacks = {success: handleLoginSuccess, error: handleLoginFailure};
                    app.api.login(args.username, args.password, options, myCallbacks);
                    return null;
                },

                /**
                 * logs current user out
                 * @param {Object} callbacks {success: function(data){}, error: function(data){}}
                 * @return
                 */
                logout: function(callbacks){
                    _userLogoutCallbacks = callbacks;
                    var myCallbacks = {success: handleLogoutSuccess, error: handleLogoutFailure};
                    app.api.logout(myCallbacks);
                    return null;
                }
            };
        }

        return instance || init();
    }()))
}(SUGAR.App));