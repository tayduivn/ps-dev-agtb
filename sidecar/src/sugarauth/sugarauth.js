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
                console.log("loggin success");
            }

            function handleLoginFailure(){
                isAuth = false;
                console.log("login fail");
            }

            function handleLogoutSuccess() {
                isAuth = false;
                console.log("loggin success");
            }

            function handleLogoutFailure(){
                isAuth = true;
                console.log("login fail");
            }


            return {
                isAuthenticated: function(){
                    //TODO add call to API to check
                    return isAuth;
                },
                login: function(args){
                    //TODO add call to API for login
                    //SUGAR.App.login(username, password, handleSuccess, handleFailure);
                    if (args.user_name == 'admin' && args.password == 'asdf'){
                        isAuth = true;
                    } else {
                        isAuth = false;
                    }

                    return isAuth;
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
    }()))
}(SUGAR.App));