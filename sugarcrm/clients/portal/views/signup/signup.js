({
    events: {
        "click [name=cancel_button]": "cancel",
        "click [name=signup_button]": "signup",
        "change select[name=country]": "render"
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

        // display the success message on rerendering
        this.signup_success = false;
    },
    render: function() {
        if (app.config && app.config.logoURL) {
            this.logoURL = app.config.logoURL
        }
        app.view.View.prototype.render.call(this);

        this.stateField = this.$('select[name=state]');
        this.countryField = this.$('select[name=country]');
        this.toggleStateField();
        return this;
    },
    toggleStateField: function() {
        if (this.countryField.val() == 'USA') {
            this.stateField.parent().show();
        } else {
            this.stateField.parent().hide();
            this.context.attributes.model.attributes.state = undefined;
        }
    },
    cancel: function() {
        app.router.goBack();
    },
    signup: function() {
        var self = this;
        var oEmail = this.model.get("email");
        if (oEmail) {
            this.model.set({
                "email": [
                    {"email_address": oEmail}
                ]
            }, {silent: true});
        }
        var validFlag = this.model.isValid();
        this.model.set({"email": oEmail}, {silent: true});
        if (validFlag) {
            $('#content').hide();
            app.alert.show('signup', {level: 'process', title: 'LBL_PORTAL_SIGNUP_PROCESS', autoClose: false});
            var contactData = {
                first_name: this.model.get("first_name"),
                last_name: this.model.get("last_name"),
                email: this.model.get("email"),
                phone_work: this.model.get("phone_work"),
                state: this.model.get("state"),
                country: this.model.get("country"),
                company: this.model.get("company"),
                jobtitle: this.model.get("jobtitle")
            };
            this.app.api.signup(contactData, null,
                {
                    error: function() {
                        app.alert.dismiss('signup');
                        $('#content').show();
                    },
                    success: function() {
                        app.alert.dismiss('signup');

                        // display the success message
                        self.signup_success = true;

                        // show a Back button
                        var buttons = self._metadata.modules[self.module].views[self.name].meta.buttons;

                        buttons[0].label = 'LBL_BACK';
                        self.options.meta.buttons = [buttons[0]];

                        self.render();
                        $('#content').show();
                    }
                });
        }
    },
    // Base metadata for Login module and login view
    _metadata: {
        _hash: '',
        "modules": {
            "Signup": {
                "fields": {
                    "first_name": {
                        "name": "first_name",
                        "type": "varchar",
                        "required": true
                    },
                    "last_name": {
                        "name": "last_name",
                        "type": "varchar",
                        "required": true
                    },
                    "email": {
                        "name": "email",
                        "type": "email",
                        "required": true
                    },
                    "phone_work": {
                        "name": "phone_work",
                        "type": "phone"
                    },
                    "state": {
                        "name": "state",
                        "type": "enum",
                        "options": "state_dom"
                    },
                    "country": {
                        "name": "country",
                        "type": "enum",
                        "options": "countries_dom",
                        "required": true
                    },
                    "company": {
                        "name": "company",
                        "type": "varchar",
                        "required": true
                    },
                    "jobtitle": {
                        "name": "jobtitle",
                        "type": "varchar"
                    },
                    "hr1": {
                        "name": "hr1",
                        "type": "hr"
                    }
                },
                "views": {
                    "signup": {
                        "meta": {
                            "buttons": [
                                {
                                    name: "cancel_button",
                                    type: "button",
                                    label: "LBL_CANCEL_BUTTON_LABEL",
                                    value: "signup",
                                    primary: false
                                },
                                {
                                    name: "signup_button",
                                    type: "button",
                                    label: "LBL_SIGNUP_BUTTON_LABEL",
                                    value: "signup",
                                    primary: true
                                }
                            ],
                            "panels": [
                                {
                                    "fields": [
                                        {name: "first_name", label: "LBL_PORTAL_SIGNUP_FIRST_NAME"},
                                        {name: "last_name", label: "LBL_PORTAL_SIGNUP_LAST_NAME"},
                                        {name: "hr1", label: ""},
                                        {name: "email", label: "LBL_PORTAL_SIGNUP_EMAIL"},
                                        {name: "phone_work", label: "LBL_PORTAL_SIGNUP_PHONE"},
                                        {name: "country", label: "LBL_PORTAL_SIGNUP_COUNTRY"},
                                        {name: "state", label: "LBL_PORTAL_SIGNUP_STATE"},
                                        {name: "hr1", label: ""},
                                        {name: "company", label: "LBL_PORTAL_SIGNUP_COMPANY"},
                                        {name: "jobtitle", label: "LBL_PORTAL_SIGNUP_JOBTITLE"}
                                    ]
                                }
                            ]
                        }
                    }
                },
                "layouts": {
                    "signup": {
                        "meta": {
                            "type": "simple",
                            "components": [
                                {view: "signup"}
                            ]
                        }
                    }
                }
            }
        }
    }
})