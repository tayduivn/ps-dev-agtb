/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Views.Base.PasswordExpiredView
 * @alias SUGAR.App.view.views.BasePasswordExpiredView
 * @extends View.View
 */
({
    plugins: ['ErrorDecoration'],
    events: {
        'click [name=save_button]': 'savePassword'
    },
    /**
     * @override
     * @param options
     */
    initialize: function(options){
        var meta = options.meta || {},
            fields = {};
        _.each(_.flatten(_.pluck(meta.panels, "fields")), function(field) {
            fields[field.name] = field;
        });
        this.fieldsToValidate = fields;
        app.view.View.prototype.initialize.call(this, options);
    },
    /**
     * @override
     * @private
     */
    _render: function() {
        var self = this;
        var message = app.lang.getAppString('LBL_PASSWORD_EXPIRATION_LOGIN');

        //Hack: Gets rid of leftover loading...
        app.alert.dismissAll();
        this.logoUrl = app.metadata.getLogoUrl();

        // Check if we have any password requirements messages and if so
        // push in to our passwordRequirements so hbs displays 'em
        this._showPasswordRequirements = false;
        this.passwordRequirements = [];
        if (app.user && app.user.has('password_requirements')) {
            this._showPasswordRequirements = true;
            var preqs = app.user.get('password_requirements');
            _.each(preqs, function(val, key) {
                self.passwordRequirements.push(val);
            });
        }
        app.view.View.prototype._render.call(this);

        //Render password expired message (after view rendered)
        if (app.user && app.user.has('password_expired_message')) {
            message = app.user.get('password_expired_message');
        }
        this.$('.password-reqs-status').text(message);
        return this;
    },
    savePassword: function() {
        var self = this, callbacks, newPass, oldPass = self.$('[name=current_password]').val();
        self.model.doValidate(this.fieldsToValidate, function(isValid) {
            if (isValid) {
                // A robot has reached into the honey pot. Do not submit (name_field not real)
                if (app.config.honeypot_on && app.config.honeypot_on === true &&
                    (self.$('input[name="name_field"]').val() || self.model.get('name_field'))) return;
                newPass = self.model.get('expired_password_update');//see change-my-password field

                if (newPass) {
                    app.alert.dismiss('changePassword');
                    app.alert.show('passreset', {level: 'process', title: app.lang.get('LBL_CHANGE_PASSWORD'), messages: app.lang.get('LBL_PROCESSING'), autoClose: false});
                    app.api.updatePassword(oldPass, newPass, {
                        success: function(data) {
                            app.alert.dismiss('passreset');
                            app.$contentEl.show();

                            //Password was valid and update successful
                            if (data && data.valid) {
                                callbacks = self.context.get("callbacks");
                                if (callbacks && callbacks.complete) {
                                    callbacks.complete();
                                }
                            } else if (data.message) {
                                //Password was deemed invalid by server. Display provided message
                                app.alert.show('password-invalid', {
                                    level: 'error',
                                    title: data.message
                                });
                            } else {
                                //Server should have provided data.message; use a generic message as fallback
                                app.alert.show('password-invalid', {
                                    level: 'error',
                                    title: app.lang.get('ERR_GENERIC_TITLE') + ': ' +
                                        app.lang.get('ERR_CONTACT_TECH_SUPPORT')
                                });
                            }
                        },
                        error: function(error) {
                            app.alert.dismiss('passreset');
                            app.error.handleHttpError(error, self);
                        }
                    });
                }
            }
        }, self);
    }
})
