/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (''License'') which can be viewed at
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
 *  (i) the ''Powered by SugarCRM'' logo and
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
        if (app.user && app.user.has('password_expired_message')) {
            message = app.user.get('password_expired_message');
        }
        //Hack: Gets rid of leftover loading...
        app.alert.dismissAll();
        app.alert.show('changePassword', {level: 'warning', title: message, autoClose: false});
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
                                app.alert.show('password-invalid', {level: 'warning', title: data.message, autoClose: true});
                            } else {
                                //Server should have provided data.message; use a generic message as fallback
                                app.alert.show('password-invalid', {level: 'warning', title: (app.lang.get('ERR_GENERIC_TITLE') + ': ' + app.lang.get('ERR_CONTACT_TECH_SUPPORT')), autoClose: true});
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
