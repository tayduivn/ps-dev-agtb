(function(app) {
    //var privateVars;
    app.augment('sugarAuth', (function() {
        var instance;

        function init(args) {
            instance = new AuthManager();
            return instance
        }

        function AuthManager() {
            var isAuth = false;
            function handleLoginSuccess() {
                isAuth = true;
                console.log("Login success");
            }

            function handleLoginFailure(){
                isAuth = false;
                console.log("Login fail");
            }

            function handleLogoutSuccess() {
                isAuth = false;
                console.log("logout success");
            }

            function handleLogoutFailure(){
                isAuth = true;
                console.log("logout fail");
            }


            return {
                /**
                 * checks if currently authenticated
                 *
                 * @return bool true if auth, false otherwise
                 */
                isAuthenticated: function(){
                    //TODO add call to API to check
                    return isAuth;
                },

                /**
                 * logs users in
                 *
                 * @param  obj of obj.user_name and obj.password
                 * @return bool true if auth, false otherwise
                 */
                login: function(args){
                    //TODO add call to API for login
                    SUGAR.App.login(args, handleLoginSuccess, handleLoginFailure);

                    return isAuth;
                },

                /**
                 * logs current user out
                 *
                 * @return bool true if logout successful, else false
                 */
                logout: function(){
                    //TODO add call to API for logout
                    isAuth = false;
                    return true;
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