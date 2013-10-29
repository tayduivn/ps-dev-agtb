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
     * User Profile wizard page for the FirstLoginWizard
     * @class View.Views.UserWizardPageView
     * @alias SUGAR.App.view.views.UserWizardPageView
     */
    extendsFrom: "WizardPageView",
    /**
     * @override
     * @param options
     */
    initialize: function(options){
        //Load the default wizard page template, if you want to.
        options.template = app.template.getView("wizard-page");
        app.view.invokeParent(this, {type: 'view', name: 'wizard-page', method: 'initialize', args:[options]});
        this.fieldsToValidate = this._fieldsToValidate(this.options.meta);
    },
    /**
     * @override
     * @returns {boolean}
     */
    isPageComplete: function(){
        return this.areAllRequiredFieldsNonEmpty;
    },
    /**
     * Prepares HTTP payload
     * @return {Object} Payload with fields we want to update
     * @protected
     */
    _prepareRequestPayload: function() {
        var payload = {},
            self = this,
            fields = _.keys(this.fieldsToValidate);
        _.each(fields, function(key) {
            payload[key] = self.model.get(key);
        });
        return payload;
    },
    /**
     * Called before we allow user to proceed to next wizard page. Does the validation and profile update.
     * @param {Function} callback The callback to call once HTTP request is completed.
     * @override
     */
    beforeNext: function(callback) {
        var self = this;
        this.getField("next_button").setDisabled(true); // temporarily disable
        this.model.doValidate(this.fieldsToValidate,
            _.bind(function(isValid) {
                var self = this;
                if (isValid) {
                    var payload = self._prepareRequestPayload();
                    app.alert.show('wizardprofile', {level: 'process', title: app.lang.getAppString('LBL_LOADING'), autoClose: false});
                    app.user.updateProfile(payload, function(err) {
                        app.alert.dismiss('wizardprofile');
                        self.updateButtons(); //re-enable buttons
                        if (err) {
                            app.logger.debug("Wizard profile update failed: " + err);
                            callback(false);
                        } else {
                            callback(true);
                        }
                    });
                } else {
                    callback(false);
                }
            }, self)
        );
    }

})
