// LoginView controller
(function(app) {

    app.view.views.LoginView = app.view.View.extend({

        className: "login",
        
        events: {
            "click #login_btn": function() {
                var loginBtn = this.$('#login_btn');
                if (loginBtn.is('[disabled=disabled]')) return false;
                
                var url = this.model.get("url");

                var args = {
                    password: this.model.get("password"),
                    username: this.model.get("username")
                };

                var validatedFields = this.model.fields;
                if(!app.isNative) {
                    // don't validate the URL field
                    validatedFields = {
                        username: this.model.fields.username,
                        password: this.model.fields.password
                    }
                }
                
                if (this.model.isValid(validatedFields)) {
                    app.alert.dismiss('field_validation_error');
                    loginBtn.attr('disabled', 'disabled').text('Loading...');

                    app.logger.debug(args.username + ":" + args.password);
                    if (app.isNative) {
                        app.api.serverUrl = app.view.views.LoginView.normalizeUrl(url);
                        app.logger.debug("REST URL: " + app.api.serverUrl);
                    }

                    app.login(args, null, {
                        success: function() {
                            app.logger.debug("logged in successfully!");
                            app.sync(function() {
                                app.logger.debug("sync success firing");
                            });
                        },
                        error: function(error) {
                            app.logger.debug("login failed: " + error);
                            loginBtn.removeAttr('disabled').text('Login');
                        }
                    });
                }
            }
        },
        
        render: function() {
            this.model.set("url", "http://", { silent: true });
            app.view.View.prototype.render.call(this, arguments);

            if (!app.isNative) {
                this.getField("url").remove();
            }

            return this;
        }

    }, {

        normalizeUrl: function(url) {
             if (url.indexOf("http") < 0) {
                 url = "http://" + url;
             }

             if ((app.config.useHttps === true) && (url.indexOf("http:") == 0)) {
                 url = "https:" + url.substr(5);
             }

             if (url.lastIndexOf("/") < (url.length - 1)) {
                 url += "/";
             }

             url += "rest/v" + app.config.restVersion;

             return url;
         }
    });

})(SUGAR.App);