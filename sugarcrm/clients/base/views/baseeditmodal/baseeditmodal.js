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
    events: {
        'click [name=save_button]': 'saveButton',
        'click [name=cancel_button]': 'cancelButton'
    },
    // Delegate events
    saveButton: function() {
        var self = this,
            createModel = this.context.get('createModel');

        self.$('[name=save_button]').attr('data-loading-text', app.lang.get('LBL_LOADING'));
        self.$('[name=save_button]').button('loading');

        // portal_flag is a required field for Notes
        createModel.set('portal_flag', true);

        // saves the related bean
        createModel.save(null, {
            relate: true,
            fieldsToValidate: this.getFields(this.module),
            success: function() {
                var view = _.extend({}, self, {model:createModel});
                app.file.checkFileFieldsAndProcessUpload(view, {
                    success: function() { self.saveComplete(); }
                });
            },
            error: function() {
                self.resetButton();
            }

        });
    },
    cancelButton: function() {
        this.$('.modal').modal('hide').find('form').get(0).reset();
        if (this.context.has('createModel')) {
            this.context.get('createModel').clear();
        }
    },
    saveComplete: function() {
        //reset the form
        this.$('.modal').modal('hide').find('form').get(0).reset();
        //reset the `Save` button
        this.resetButton();
        //add the new model to the collection
        this.collection.fetch({relate:true});
    },
    resetButton: function() {
        this.$('[name=save_button]').button('reset');
    }
})
