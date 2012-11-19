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
 * View that displays edit view on a profile. 
 * @class View.Views.ProfileEditView
 * @alias SUGAR.App.layout.ProfileEditView
 * @extends View.View
 */
({
    events: {
        'click [name=save_button]': 'saveModel', // bottom save button
        'click a.password': 'changePassword' 
    },
    initialize: function(options) {
        this.options.meta = app.metadata.getView('Contacts', 'edit');
        app.view.View.prototype.initialize.call(this, options);
        this.meta.type = 'edit'; // will use edit sugar fields
        this.template = app.template.get("edit");
        this.fallbackFieldTemplate = "edit";
        this.context.off("subnav:save", null, this);
        this.context.on("subnav:save", this.saveModel, this);
    },
    render: function() {
        var self = this, currentUserAttributes;

        if(app.user.isSupportPortalUser()) {
            currentUserAttributes = {id: app.user.get('id')}; 
            self.loadCurrentUser(currentUserAttributes, function(data) {
                if(data) {
                    app.user.addSalutationToFullName(data);
                    self.setModelAndContext(data);
                    app.view.View.prototype.render.call(self);
                    self.$('a.password').text(app.lang.get('LBL_CONTACT_EDIT_PASSWORD_LNK_TEXT'));
                    self.renderSubnav(data);
                } 
            });
        } else {
            app.router.goBack();
            app.alert.show('not_portal_enabled_user', {level:'error', title: app.lang.getAppString('LBL_PORTAL_PAGE_NOT_AVAIL'), messages: app.lang.getAppString('LBL_PORTAL_NOT_ENABLED_MSG'), autoClose: true});
        }
    },
    loadCurrentUser: function(currentUserAttributes, cb) {
        var self = this;
        app.alert.show('fetch_edit_contact_record', {level:'process', title:app.lang.getAppString('LBL_PORTAL_LOADING')});
        app.api.records("read", "Contacts", currentUserAttributes, null, {
            success: function(data) {
                app.alert.dismiss('fetch_edit_contact_record');
                cb(data);
            },
            error: function(error) {
                app.alert.dismiss('fetch_edit_contact_record');
                app.error.handleHttpError(error, self);
            }
        });
    },
    renderSubnav: function(data) {
        var self = this, fullName = '', subnavModel = null;
        if (self.context.get('subnavModel')) {
            fullName = data.name ? data.full_name : data.first_name +' '+data.last_name;
            self.context.get('subnavModel').set({
                'title': fullName,
                'meta': self.meta
            });
            
            // Bypass subnav click handler
            $('.subnav').find('[name=save_button]').on('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                self.saveModel();
            });
        }
    },
    setModelAndContext: function(data) {
        this.model = app.data.createBean("Contacts", data);
        this.context.set({
            'model': this.model,
            'module': 'Contacts'
        });
    },
    changePassword: function() {
        // triggers an event to show the modal
        this.layout.trigger("app:view:password:editmodal", this);
        return false;
    },
    
    saveModel: function() {
        var self = this, options;
        app.alert.show('save_profile_edit_view', {level:'process', title:app.lang.getAppString('LBL_PORTAL_SAVING')});
        options = {
            success: function() {
                app.alert.dismiss('save_profile_edit_view');
                self.checkFileFieldsAndProcessUpload(self.model, {
                    success: function () {

                        var langKey = self.model.get('preferred_language');
                        if (langKey && langKey != app.lang.getLanguage())
                            app.lang.setLanguage(langKey,{},{noUserUpdate: true});

                        app.router.navigate('profile', {trigger: true});
                    }
                });
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
