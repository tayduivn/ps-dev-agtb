(function(app) {
    //var privateVars;

    app.sugarAuth = (function() {
        var instance;

        function init(args) {
            instance = new AuthManager();
            return instance
        }

        function AuthManager() {
            return {
                isAuthenticated: function(){
                    return true;
                },
                login: function(username, password){
                    return true;
                },
                logout: function(){
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