({
    extends:'BaseeditmodalView',
    initialize: function(options) {
        var self = this;
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
    _render: function() {
        var self = this;
        this.saveButtonWasClicked = false;
        app.view.View.prototype._render.call(this);
        this.events = _.extend(this.events, {
            "focusin input[name=new_password]" : "verifyCurrentPassword",
            "focusin input[name=confirm_password]" : "verifyCurrentPassword"
        });
        this.delegateEvents();
    },
    verifyCurrentPassword: function() {
        var self = this, currentPassword;
        currentPassword = self.$('[name=current_password]').val();

        // If user leaving old password text field, actually entered something, and we're sure the user
        // hasn't already clicked save (potentially a race condition if this completes after saveComplete, etc.)
        if(currentPassword && currentPassword.length && !self.saveButtonWasClicked) {
            app.api.verifyPassword(currentPassword, {
                success: function(data) {
                    // Since we're essentially looking for valid:true, this works ;=)
                    if(!self.checkUpdatePassWorked(data)) {
                        app.alert.show('pass_verification_failed', {
                            level: 'error',
                            title: app.lang.get('LBL_PORTAL_LOGIN_PASSWORD'),
                            messages: app.lang.get('LBL_PORTAL_PASSWORD_VERIFICATION_FAILED'),
                            autoClose: true});
                        self.$('[name=current_password]').val('');
                        self.$('[name=current_password]').focus();
                    } else {
                        app.alert.dismiss('pass_verification_failed');
                    }
                },
                error: function(error) {
                    app.error.handleHttpError(error, self);
                    self.resetButton();
                }
            });
        }
    },
    // Since we don't have a true Bean/meta driven validation for matching two temp fields 
    // (password and confirmation password), etc., we manually add validation errors here
    handleCustomValidationError: function(field, errorMsg) {
        field.find('.control-group').addClass("error");
        field.find('.help-block').html("");
        field.find('.controls').addClass('input-append');
        field.find('.help-block').append(errorMsg);
        field.find('.add-on').remove();
        field.find('.controls').find('input:last').after('<span class="add-on"><i class="icon-exclamation-sign"></i></span>');
    },
    verify: function(contactModel) {
        var self = this, currentPassword, password, confirmPassword, confirmPasswordField, isError=false,
            passwordField, maxLen, currentPasswordField;

        self.$('[name=save_button]').button().text(app.lang.get('LBL_LOADING'));
        currentPasswordField = this.$('[name=current_password]');
        currentPassword = currentPasswordField.val();
        // TODO: Here we will call a password verification endpoint which does not yet exist

        passwordField = this.$('[name=new_password]');
        password = passwordField.val();
        confirmPasswordField = this.$('[name=confirm_password]');
        confirmPassword = confirmPasswordField.val();
        
        if(!currentPassword) {
            self.handleCustomValidationError(currentPasswordField.parents('.control-group'),app.lang.get('ERROR_FIELD_REQUIRED'));
            isError=true;
        }
        if(!password) {
            self.handleCustomValidationError(passwordField.parents('.control-group'),app.lang.get('ERROR_FIELD_REQUIRED'));
            isError=true;
        }
        if(!confirmPassword) {
            self.handleCustomValidationError(confirmPasswordField.parents('.control-group'),app.lang.get('ERROR_FIELD_REQUIRED'));
            isError=true;
        }
        if(password !== confirmPassword) {
            self.$('[name=save_button]').button().text(app.lang.get('LBL_SAVE_BUTTON_LABEL'));
            self.handleCustomValidationError(confirmPasswordField.parents('.control-group'),app.lang.get('LBL_PORTAL_PASSWORDS_MUST_MATCH'));
            isError=true;
        }
        maxLen = parseInt(app.metadata.getModule('Contacts').fields.portal_password.len, 10);
        if(confirmPassword.length > maxLen) {
            self.handleCustomValidationError(confirmPasswordField.parents('.control-group'), app.error.getErrorString('ERROR_MAX_FIELD_LENGTH', maxLen) );
            isError=true;
        }
        return !isError;
    },
    saveButton: function() {
    var self = this, contactModel = this.context.get('contactModel');
        if(self.verify(contactModel)) {
            self.saveModel(contactModel);
        } else {
            self.$('[name=save_button]').button().text(app.lang.get('LBL_SAVE_BUTTON_LABEL'));
        }
    },
    saveModel: function(contactModel) {
        var self = this, 
            oldPass = contactModel.get('current_password'),
            newPass = contactModel.get('new_password');

        this.saveButtonWasClicked = true;

        // Add the new pass to portal_password and remove temp fields
        contactModel.unset('current_password', {silent: true});
        contactModel.unset('confirm_password', {silent: true});
        contactModel.unset('new_password', {silent: true});

        app.alert.show('passreset', {level: 'error', title: app.lang.get('LBL_PORTAL_LOGIN_PASSWORD'), autoClose: false});

        app.api.updatePassword(oldPass, newPass, {
            success: function(data) {
                app.alert.dismiss('passreset');
                contactModel.set({'portal_password': 'value_setvalue_setvalue_set'}, {silent: true});
                if(self.checkUpdatePassWorked(data)) {
                    self.saveComplete();
                } else {
                    app.alert.show('pass_update_failed', {
                        level: 'error',
                        title: app.lang.get('LBL_PORTAL_LOGIN_PASSWORD'),
                        messages: app.lang.get('LBL_PORTAL_PASSWORD_UPDATE_FAILED'),
                        autoClose: true});
                    self.$('.modal').modal().find('input:text, input:password').val('');
                    self.$('[name=save_button]').button().text(app.lang.get('LBL_SAVE_BUTTON_LABEL'));
                }
            },
            error: function(error) {
                app.alert.dismiss('passreset');
                app.error.handleHttpError(error, self);
                self.resetButton();
            }
        });
    },
    checkUpdatePassWorked: function(data) {
        if(!data || !data.current_user || !data.current_user.valid) {
            return false;
            app.logger.error("Failed to update password. "); 
        } 
        return true;
    },
    saveComplete: function() {
        //reset the form
        this.$('.modal').modal('hide').find('form').get(0).reset();
        //reset the `Save` button
        this.resetButton();
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
