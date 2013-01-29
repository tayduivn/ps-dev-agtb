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
 * View that displays edit view on a model
 * @class View.Views.EditView
 * @alias SUGAR.App.layout.EditView
 * @extends View.View
 */
({
    events: {
        'click [name=save_button]': 'saveModel'
    },
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.context.off("subnav:save", null, this);
        this.context.on("subnav:save", this.saveModel, this);
    },
    saveModel: function() {
        var self = this;

        // TODO we need to dismiss this in global error handler
        app.alert.show('save_edit_view', {level: 'process', title: app.lang.getAppString('LBL_PORTAL_SAVING')});
        this.model.save(null, {
            success:function () {

                app.file.checkFileFieldsAndProcessUpload(self.model, {
                    success: function () {
                        app.alert.dismiss('save_edit_view');
                        app.navigate(self.context, self.model, 'detail');
                    }
                },
                { deleteIfFails: false});

            },
            fieldsToValidate: this.getFields(this.module)
        });
    },
    _renderHtml: function() {
        app.view.View.prototype._renderHtml.call(this);
        if (this.model.id) {
            this.model.on("change", function() {
                if (this.context.get('subnavModel')) {
                    this.context.get('subnavModel').set({
                        'title': app.lang.get('LBL_EDIT_BUTTON', this.module),
                        'meta': this.meta,
                        'fields': this.fields
                    });
                }
            }, this);
        } else {
            if (this.context.get('subnavModel')) {
                this.context.get('subnavModel').set({
                    'title': app.lang.get('LBL_NEW_FORM_TITLE', this.module),
                    'meta': this.meta,
                    'fields': this.fields
                });
            }
        }
    }
})
