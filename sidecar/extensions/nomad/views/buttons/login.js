// Login button view controller
// Alternatively, we can use LoginView controller to wire up events (views/login.js)
(function(app) {

    app.view.fields.loginButton = app.view.Field.extend({
        events: {
            "click": function() {
                var args = {password: this.model.get("password"), username: this.model.get("username")};
                app.api.login(args, {
                    success: function() {
                        app.logger.debug("logged in successfully!");
                        app.sync(function() {
                            app.logger.debug("Firing app.sync");
                        });
                }});
            }
        }
    });

})(SUGAR.App);