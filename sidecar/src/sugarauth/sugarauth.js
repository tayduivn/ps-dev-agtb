(function(app) {
    //var privateVars;
    app.sugarAuth = (function() {
        var instance;

        function init(args) {
            instance = new AuthManager();
            return instance
        }

        function AuthManager() {
            var isAuth = false;
            function handleLoginSuccess() {
                isAuth = true;
                console.log("loggin success");
            }

            function handleLoginFailure(){
                isAuth = false;
                console.log("login fail")
            }

            function handleLogoutSuccess() {
                isAuth = true;
                console.log("loggin success");
            }

            function handleLogoutFailure(){
                isAuth = false;
                console.log("login fail")
            }


            return {
                isAuthenticated: function(){
                    //TODO add call to API to check
                    return isAuth;
                },
                login: function(username, password){
                    //TODO add call to API for login
                    var result = false;
                    //result = SUGAR.App.login(username, password, handleSuccess, handleFailure);
                    return result;
                },
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
    }())

    return app;
}(SUGAR.App));