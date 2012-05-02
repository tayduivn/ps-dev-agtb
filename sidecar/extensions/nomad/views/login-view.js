// LoginView controller
// Alternatively, we can use loginButton view controller (views/buttons/login.js)
(function(app) {

    app.view.views.LoginView = app.view.View.extend({
        events: {
            "click #login_btn": function() {
                var self = this;
                var model = this.context.get("model");
                var url = model.get("url");
                var args = {password: model.get("password"), username: model.get("username")};
                app.logger.debug(args.username + ":" + args.password + "@" + url);
                app.api.login(args, null, {
                    success: function() {
                        app.logger.debug("logged in successfully!");
                        app.sync(function() {
                            app.logger.debug("sync success firing");
                        });
                    },
                    error: function(error) {
                        app.logger.debug("login failed: " + error);
                    }
                });
            }
        }
    });

})(SUGAR.App);