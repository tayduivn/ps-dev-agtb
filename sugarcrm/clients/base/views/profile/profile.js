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
 * View that displays profile detail.
 * @class View.Views.ProfileView
 * @alias SUGAR.App.view.ProfileView
 * @extends View.Views.DetailView
 */
({
    extendsFrom: "DetailView",
    initialize: function(options) {
        this.options.meta   = app.metadata.getView(this.options.module, 'detail');
        app.view.views.DetailView.prototype.initialize.call(this, options);
        this.template = app.template.get("detail");
    },
    getFullName: function() {
        var full_name = this.model.get('full_name') || this.model.get('first_name') + ' ' + this.model.get('last_name') || this.model.get('name'),
            salutation = this.model.get('salutation');
        if (!_.isEmpty(salutation)) {
            var salutation_dom = app.lang.getAppListStrings(this.model.fields.salutation.options);
            salutation = salutation_dom[salutation] || salutation;
            full_name = salutation + ' ' + full_name;
        }
        return full_name;
    },
    bindDataChange:function () {
        if (this.model) {
            this.model.on("change", function () {
                this.fieldsToDisplay = _.toArray(this.model.fields).length;
                if (this.context.get('subnavModel')) {
                    this.context.get('subnavModel').set({
                        'title':this.getFullName(),
                        'meta':this.meta
                    });
                    this.model.isNotEmpty = true;
                    this.render();
                }
            }, this);
        }
    }
})

