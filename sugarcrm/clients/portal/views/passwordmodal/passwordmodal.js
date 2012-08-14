({
    extends:'BaseeditmodalView',
    initialize: function(options) {
        this.options.meta = this._meta.meta;
        app.view.View.prototype.initialize.call(this, options);
        this.fallbackFieldTemplate = "edit";

        if (this.layout) {
            this.layout.on("app:view:password:editmodal", function(profileEditView) {
                this.context.set('contactModel', profileEditView.context.get('model'));
                this.render();
                this.$('.modal').modal('show');
                this.context.get('contactModel').on("error:validation", function() {
                    this.resetButton();
                }, this);
            }, this);
        }
        this.bindDataChange();
    },
    saveButton: function() {
debugger
        var self = this,
            contactModel = this.context.get('contactModel');

        self.$('[name=save_button]').button('loading');

        var contactData = {
            id: contactModel.id
        };
        app.api.updatePassword(contactData, 'foobarbaz', {
            success: function(data) {
                console.log("SUCCESS: data: ", data);
            },
            error: function(error) {
                console.log("ERROR: ", error);
                app.error.handleHttpError(error, self);
            }
        });
/*
        // saves the related bean
        if(contactModel.isValid()) {
            contactModel.save(null, {
                fieldsToValidate: this.getFields(this.module),
                success: function() {
                    self.saveComplete();
                },
                error: function() {
                    self.resetButton();
                }
            });
        }
            */
    },
    _meta: 
        {
            "meta": {
                "buttons": [
                    {
                        "name": "save_button",
                        "type": "button",
                        "label": "Save",
                        "value": "save",
                        "class": "save-profile",
                        "primary": true
                    },
                    {
                        "name": "cancel_button",
                        "type": "button",
                        "label": "Cancel",
                        "value": "cancel",
                        "events": {
                            "click": "function(){ window.history.back(); }"
                        },
                        "primary": false
                    }
                ],
                "panels": [
                    {
                        "label": "default",
                        "fields": [
                            {
                                "name": "current_password",
                                "type": "password",
                                "label": "LBL_OLD_PORTAL_PASSWORD",
                                "displayParams": {
                                    "colspan": 2
                                }
                            },
                            {
                                "name": "new_password",
                                "type": "password",
                                "label": "LBL_PORTAL_PASSWORD",
                                "displayParams": {
                                    "colspan": 2
                                }
                            },
                            {
                                "name": "confirm_password",
                                "type": "password",
                                "label": "LBL_CONFIRM_PORTAL_PASSWORD",
                                "displayParams": {
                                    "colspan": 2
                                }
                            }
                        ]
                    }
                ]
            }
        }
    
  })
