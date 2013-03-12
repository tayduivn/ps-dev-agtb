({
    events: {
        "click .login-submit": "login",
        "keypress": "handleKeypress"
    },

    initialize: function(options) {
        // Adds the metadata for the Login module
        app.metadata.set(this._metadata);
        app.data.declareModels();

        // Reprepare the context because it was initially prepared without metadata
        app.controller.context.prepare(true);

        // Attach the metadata to the view
        this.options.meta = this._metadata.modules[this.options.module].views[this.options.name].meta;
        app.view.View.prototype.initialize.call(this, options);

        // use modal template for the fields
        this.fallbackFieldTemplate = "modal";
    },

    handleKeypress: function(e) {
        if (e.keyCode === 13) {
            this.$("input").trigger("blur");
            this.login();
        }
    },

    _render: function() {
        if (app.config && app.config.logoURL) {
            this.logoURL = app.config.logoURL;
        }
        app.view.View.prototype._render.call(this);
        this.refreshAddtionalComponents();
        return this;
    },
    refreshAddtionalComponents: function() {
        _.each(app.additionalComponents, function(component) {
            component.render();
        });
    },
    login: function() {
        var self = this;
        if (this.model.isValid()) {
            app.$contentEl.hide();
            var args = {password: this.model.get("password"), username: this.model.get("username")};

            app.alert.show('login', {level: 'process', title: app.lang.getAppString('LBL_LOADING'), autoClose: false});
            app.login(args, null, {
                error: function() {
                    app.$contentEl.show();
                    app.logger.debug("login failed!");
                },
                success: function() {
                    app.logger.debug("logged in successfully!");
                    app.events.on('app:sync:complete', function() {
                        app.logger.debug("sync in successfully!");
                        self.refreshAddtionalComponents();
                        app.$contentEl.show();
                    });
                },
                complete: function() {
                    app.alert.dismiss('login');
                }
            });
        }
    },

    _metadata : {
        _hash: '',
        "modules": {
            "Login": {
                "fields": {
                    "username": {
                        "name": "username",
                        "type": "base",
                        "required": true
                    },
                    "password": {
                        "name": "password",
                        "type": "password",
                        "required": true
                    }
                },
                "views": {
                    "login": {
                        "meta": {
                            "buttons": [
                                {
                                    name: "login_button",
                                    type: "button",
                                    label: "LBL_LOGIN_BUTTON_LABEL",
                                    'css_class': "login-submit",
                                    value: "login",
                                    primary: true
                                }
                            ],
                            "panels": [
                                {
                                    "fields": [
                                        {name: "username", label: "LBL_LOGIN_USERNAME"},
                                        {name: "password", label: "LBL_LOGIN_PASSWORD"}
                                    ]
                                }
                            ]
                        }
                    }
                },
                "layouts": {
                    "login": {
                        "meta": {
                            //Default layout is a single view
                            "type": "simple",
                            "components": [
                                {view: "login"}
                            ]
                        }
                    }
                }
            }
        }
    }
})
