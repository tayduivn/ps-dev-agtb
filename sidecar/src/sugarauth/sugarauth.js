(function(app) {
    //var privateVars;
    app.augment('sugarAuth', (function() {
        var instance;
        var api;
        var _userLoginCallbacks;
        var _userLogoutCallbacks;
        function init(args) {
            api = SUGAR.Api.getInstance();
            instance = new AuthManager();
            return instance
        }

        function AuthManager() {
            var isAuth = false;
            function handleLoginSuccess(data) {
                isAuth = true;
                if (_userLoginCallbacks && _userLoginCallbacks.success) {
                    _userLoginCallbacks.success(data);
                }
            }

            function handleLoginFailure(data){
                isAuth = false;
                if (_userLoginCallbacks && _userLoginCallbacks.error) {
                    _userLoginCallbacks.error(data);
                }
            }

            function handleLogoutSuccess(data) {
                isAuth = false;
                if (_userLogoutCallbacks && _userLogoutCallbacks.success) {
                    _userLogoutCallbacks.success(data);
                }
            }

            function handleLogoutFailure(data){
                isAuth = true;
                if (_userLogoutCallbacks && _userLogoutCallbacks.error) {
                    _userLogoutCallbacks.error(data);
                }
            }


            return {
                /**
                 * checks if currently authenticated
                 *
                 * @return bool true if auth, false otherwise
                 */
                isAuthenticated: function(){
                    return api.isAuthenticated();
                },

                /**
                 * logs users in
                 *
                 * @param  obj of obj.user_name and obj.password
                 * @return bool true if auth, false otherwise
                 */
                login: function(args, callbacks){
                    if(callbacks){
                        _userLoginCallbacks = callbacks;
                    }
                    var options = args.options || {};
                    var myCallbacks = {success: handleLoginSuccess, error: handleLoginFailure};
                    api.login(args.user_name, args.password, options, myCallbacks);
                    return null;
                },

                /**
                 * logs current user out
                 *
                 * @return bool true if logout successful, else false
                 */
                logout: function(callbacks){
                    _userLogoutCallbacks = callbacks;
                    var myCallbacks = {success: handleLogoutSuccess, error: handleLogoutFailure};
                    api.logout(myCallbacks);
                    return null;
                }
            };
        }

        return {
            getInstance: function(args) {
                return instance || init(args);
            }
        };
    }()))
}(SUGAR.App));