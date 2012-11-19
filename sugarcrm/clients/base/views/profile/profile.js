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
 * View that displays search results.
 * @class View.Views.ProfileView
 * @alias SUGAR.App.layout.ProfileView
 * @extends View.View
 */
    events: {},
    initialize: function(options) {
        this.options.meta   = app.metadata.getView('Contacts', 'detail');
        app.view.View.prototype.initialize.call(this, options);
        this.template = app.template.get("detail");
        this.fallbackFieldTemplate = "detail"; // will use detail sugar fields
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
        app.alert.show('fetch_contact_record', {level:'process', title:app.lang.getAppString('LBL_PORTAL_LOADING')});
        app.api.records("read", "Contacts", currentUserAttributes, null, {
            success: function(data) {
                app.alert.dismiss('fetch_contact_record');
                cb(data);
                self.$('.modelNotLoaded').hide();
                self.$('.modelLoaded').show();
            },
            error: function(error) {
                app.alert.dismiss('fetch_contact_record');
                app.error.handleHttpError(error, self);
            }
        });
    },
    /**
     * Updates model for this contact.
     */
    setModelAndContext: function(data) {
        this.model = app.data.createBean("Contacts", data);
        this.model.isNotEmpty = true;
        this.context.set({
            'model': this.model,
            'module': 'Contacts',
            _dataFetched: true
        });
    },
    renderSubnav: function(data) {
        var self = this, subnavModel = null;
        if (self.context.get('subnavModel')) {
            self.context.get('subnavModel').set({
                'title': data.full_name,
                'meta': self.meta
            });
        }
    }
})

