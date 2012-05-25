// LoginView controller
// Alternatively, we can use loginButton view controller (views/buttons/login.js)
(function(app) {

    app.view.views.LoginView = app.view.View.extend({

        className: "login",

        events: {
            "click #login_btn": function() {
                var self = this;
                var model = this.context.get("model");
                var url = model.get("url");
                var args = {password: model.get("password"), username: model.get("username")};
                app.logger.debug(args.username + ":" + args.password + "@" + url);
                
                if(this.model.isValid()) {
                    app.alert.dismiss('field_validation_error');
                    var loginBtn = this.$('#login_btn');
                    loginBtn.text('Loading...');
                    app.login(args, null, {
                        success: function() {
                            app.logger.debug("logged in successfully!");
                            app.sync(function() {
                                app.logger.debug("sync success firing");
                            });
                        },
                        error: function(error) {
                            app.logger.debug("login failed: " + error);
                            loginBtn.text('Login');
                        }
                    });
                }
            }
        },
        
        render: function() {
            app.view.View.prototype.render.call(this, arguments);
            if(!app.isNative) {
                this.$('#url').hide();
            }
            return this;
        }
    });

})(SUGAR.App);