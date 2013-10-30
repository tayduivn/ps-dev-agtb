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
    /**
     * User Locale wizard page for the FirstLoginWizard
     * @class View.Views.UserLocaleWizardPageView
     * @alias SUGAR.App.view.views.UserLocaleWizardPageView
     */
    extendsFrom: "UserWizardPageView",
    TIME_ZONE_KEY: 'timezone',
    TIME_PREF_KEY: 'timepref',
    DATE_PREF_KEY: 'datepref',
    NAME_FORMAT_KEY: 'default_locale_name_format',
    /**
     * @override
     * @param options
     */
    initialize: function(options) {
        var self = this;
        options.template = app.template.getView("wizard-page");
        app.view.invokeParent(self, {type: 'view', name: 'user-wizard-page', method: 'initialize', args:[options]});
        // Preset the user prefs for formats
        if (this.model) {
            this.model.set(this.TIME_ZONE_KEY, (app.user.getPreference(this.TIME_ZONE_KEY) || ''));
            this.model.set(this.TIME_PREF_KEY, (app.user.getPreference(this.TIME_PREF_KEY) || ''));
            this.model.set(this.DATE_PREF_KEY, (app.user.getPreference(this.DATE_PREF_KEY) || ''));
            this.model.set(this.NAME_FORMAT_KEY, (app.user.getPreference(this.NAME_FORMAT_KEY) || ''));
        }
    },
    _render: function(){
        var self = this;
        // Prepare the metadata so we can prefetch select2 locale options
        this._prepareFields(function() {
            if (!self.disposed) {
                self.fieldsToValidate = self._fieldsToValidate(self.meta);
                app.view.invokeParent(self, {type: 'view', name: 'user-wizard-page', method: '_render'});
            }
        });
    },
    _prepareFields: function(callback) {
        var self = this;
        app.user.loadLocale(function(localeOptions) {
            // Populate each field def of type enum with returned locale options and use user's pref as displayed
            _.each(self.meta.panels[0].fields, function(fieldDef) {
                var opts = localeOptions[fieldDef.name];
                if (opts) {
                    fieldDef.options = opts;
                }
            });
            callback();
        });
    },
    /**
     * Called before we allow user to proceed to next wizard page. Does the validation and locale update.
     * @param {Function} callback The callback to call once HTTP request is completed.
     * @override
     */
    beforeNext: function(callback) {
        this.getField("next_button").setDisabled(true);  //temporarily disable
        this.model.doValidate(this.fieldsToValidate,
            _.bind(function(isValid) {
                var self = this;
                if (isValid) {
                    var payload = this._prepareRequestPayload();
                    app.alert.show('wizardlocale', {
                        level: 'process',
                        title: app.lang.getAppString('LBL_LOADING'),
                        autoClose: false
                    });
                    // 'ut' is, historically, a special flag in user's preferences that is
                    // generally marked truthy upon timezone getting saved. It's also used
                    // to semantically represent "is the user's instance configured"
                    payload['ut'] = true;
                    app.user.updatePreferences(payload, function(err) {
                        app.alert.dismiss('wizardlocale');
                        self.updateButtons();  //re-enable buttons
                        if (err) {
                            app.logger.debug("Wizard locale update failed: " + err);
                            callback(false);
                        } else {
                            callback(true);
                        }
                    });
                } else {
                    callback(false);
                }
            }, this)
        );
    }

})
