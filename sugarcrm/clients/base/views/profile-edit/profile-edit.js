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
/**
 * View that displays profile edit.
 * @class View.Views.ProfileEditView
 * @alias SUGAR.App.view.ProfileEditView
 * @extends View.Views.EditView
 */
({
    extendsFrom: "EditView",
    events: {
        'click [name=save_button]': 'saveModel', // bottom save button
        'click a.password': 'changePassword'
    },
    initialize: function(options) {
        this.options.meta   = app.metadata.getView(this.options.module, 'edit');
        app.view.views.EditView.prototype.initialize.call(this, options);
        this.template = app.template.get("edit");
        this.fallbackFieldTemplate = "edit";
        this.model.on("change", function() {
            this.$('a.password')
                .attr('href', 'javascript:void(0)').
                text(app.lang.get('LBL_CONTACT_EDIT_PASSWORD_LNK_TEXT'));
        }, this);
    },
    changePassword: function() {
        // triggers an event to show the modal
        this.layout.trigger("app:view:password:editmodal");
        return false;
    },
    saveModel: function() {
        var self = this, options;
        app.alert.show('save_profile_edit_view', {level:'process', title:app.lang.getAppString('LBL_SAVING')});
        options = {
            success: function() {
                app.alert.dismiss('save_profile_edit_view');
                app.file.checkFileFieldsAndProcessUpload(self.model, {
                    success: function () {
                        var langKey = self.model.get('preferred_language');
                        if (langKey && langKey != app.lang.getLanguage())
                            app.lang.setLanguage(langKey,{},{noUserUpdate: true});

                        app.router.navigate('profile', {trigger: true});
                    }
                },
                { deleteIfFails: false});
            },
            error: function(error) {
                app.alert.dismiss('save_profile_edit_view');
                app.error.handleHttpError(error);
            },
            fieldsToValidate: self.getFields(this.model.module)
        };

        // So we don't overwrite password
        self.model.unset('portal_password', {silent: true});
        self.model.unset('portal_password1', {silent: true});
        self.model.save(null, options);
    }
})
