/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
({
    extendsFrom:'BaseeditmodalView',
    fallbackFieldTemplate: 'edit',
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        if (this.layout) {
            this.layout.on("app:view:password:editmodal", function() {
                this.model = this.context.get('model');
                this.render();
                this.$('.modal').modal('show');
                this.model.on("error:validation", function() {
                    this.resetButton();
                }, this);
            }, this);
        }
        this.bindDataChange();
    },
    _renderHtml: function() {
        this.saveButtonWasClicked = false;
        this.events = _.clone(this.events);
        _.extend(this.events, {
            "focusin input[name=new_password]" : "verifyCurrentPassword",
            "focusin input[name=confirm_password]" : "verifyCurrentPassword"
        });
        app.view.View.prototype._renderHtml.call(this);
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
                            title: app.lang.get('LBL_PASSWORD', self.module),
                            messages: app.lang.get('ERR_PASSWORD_MISMATCH', self.module),
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
        field = field.parents('.control-group')
        field.addClass('error');// Note the field is row fluid control group
        field.find('.help-block').html("");
        field.find('.help-block').append(errorMsg);
        field.find('.add-on').remove();
        field.find('input:last').after('<span class="add-on"><i class="icon-exclamation-sign"></i></span>');
    },
    setLoading: function() {
        this.$('[name=save_button]').attr('data-loading-text', app.lang.get('LBL_LOADING'));
        this.$('[name=save_button]').button('loading');
    },
    verify: function() {
        var self = this, currentPassword, password, confirmPassword, confirmPasswordField, isError=false,
            passwordField, maxLen, currentPasswordField;
        self.setLoading();

        currentPasswordField = this.$('[name=current_password]');
        currentPassword = currentPasswordField.val();
        // TODO: Here we will call a password verification endpoint which does not yet exist

        passwordField = this.$('[name=new_password]');
        password = passwordField.val();
        confirmPasswordField = this.$('[name=confirm_password]');
        confirmPassword = confirmPasswordField.val();

        if(!currentPassword) {
            self.handleCustomValidationError(currentPasswordField,app.lang.get('ERR_ENTER_OLD_PASSWORD', self.module));
            isError=true;
        }
        if(!password) {
            self.handleCustomValidationError(passwordField,app.lang.get('ERR_ENTER_NEW_PASSWORD', self.module));
            isError=true;
        }
        if(!confirmPassword) {
            self.handleCustomValidationError(confirmPasswordField,app.lang.get('ERR_ENTER_CONFIRMATION_PASSWORD', self.module));
            isError=true;
        }
        if(password !== confirmPassword) {
            self.setLoading();
            self.handleCustomValidationError(confirmPasswordField,app.lang.get('ERR_REENTER_PASSWORDS'), self.module);
            isError=true;
        }

        var passwordField = self.context.get('passwordField'),
            mod = app.metadata.getModule(self.module);
        maxLen = mod[passwordField] ? parseInt(mod[passwordField].len, 10) : 0;
        if(maxLen > 0 && confirmPassword.length > maxLen) {
            self.handleCustomValidationError(confirmPasswordField, app.error.getErrorString('ERROR_MAX_FIELD_LENGTH', maxLen) );
            isError=true;
        }
        return !isError;
    },
    saveButton: function() {
        if(this.verify()) {
            this.saveModel();
        } else {
            this.resetButton();
        }
    },
    saveModel: function() {
        var self = this,
            oldPass = self.model.get('current_password'),
            newPass = self.model.get('new_password');

        this.saveButtonWasClicked = true;

        app.alert.show('passreset', {level: 'process', title: app.lang.get('LBL_PASSWORD', self.module), messages: app.lang.get('LBL_PROCESSING', self.module), autoClose: false});

        app.api.updatePassword(oldPass, newPass, {
            success: function(data) {
                app.alert.dismiss('passreset');
                if(self.checkUpdatePassWorked(data)) {
                    self.saveComplete();
                } else {
                    app.alert.show('pass_update_failed', {
                        level: 'error',
                        title: app.lang.get('LBL_PASSWORD', self.module),
                        messages: app.lang.get('LBL_CANNOT_SEND_PASSWORD'),
                        autoClose: true});
                    self.$('.modal').modal().find('input:text, input:password').val('');
                    self.resetButton();
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
        if(!data || !data.valid) {
            app.logger.error("Failed to update password.");
            return false;
        }
        return true;
    },
    saveComplete: function() {
        var self = this;
        // Remove temp fields
        self.model.unset('current_password', {silent: true});
        self.model.unset('confirm_password', {silent: true});
        self.model.unset('new_password', {silent: true});

        //reset the form
        self.$('.modal').modal('hide').find('form').get(0).reset();
        //reset the `Save` button
        self.resetButton();
        //"Your password has been successfully updated."
        app.alert.show('pass_successfully_changes', {
            level: 'success',
            title: app.lang.get('LBL_PASSWORD', self.module),
            messages: app.lang.get('LBL_NEW_USER_PASSWORD_1', self.module),
            autoClose: true});
    }
})
