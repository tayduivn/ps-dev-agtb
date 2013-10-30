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
    extendsFrom: 'ConvertResultsView',

    /**
     * Build a collection of associated models and re-render the view
     */
    populateResults: function() {
        var model;

        //only show related records if lead is converted
        if (!this.model.get('converted')) {
            return;
        }

        this.associatedModels.reset();

        model = this.buildAssociatedModel('Contacts', 'contact_id', 'contact_name');
        if (model) {
            this.associatedModels.push(model);
        }
        model = this.buildAssociatedModel('Accounts', 'account_id', 'account_name');
        if (model) {
            this.associatedModels.push(model);
        }
        model = this.buildAssociatedModel('Opportunities', 'opportunity_id', 'opportunity_name');
        if (model) {
            this.associatedModels.push(model);
        }
        app.view.View.prototype.render.call(this);
    },

    /**
     * Build an associated model based on given id & name fields on the Lead record
     *
     * @param moduleName
     * @param idField
     * @param nameField
     * @return {*} model or false if id field is not set on the lead
     */
    buildAssociatedModel: function(moduleName, idField, nameField) {
        var moduleSingular = app.lang.getAppListStrings("moduleListSingular"),
            rowTitle,
            model;

        if (_.isEmpty(this.model.get(idField))) {
            return false;
        }

        rowTitle = app.lang.get(
            'LBL_CONVERT_MODULE_ASSOCIATED',
            this.module,
            {'moduleName': moduleSingular[moduleName]}
        );

        model = app.data.createBean(moduleName, {
            id: this.model.get(idField),
            name: this.model.get(nameField),
            row_title: rowTitle,
            _module: moduleName,
            target_module: moduleName
        });
        model.module = moduleName;
        return model;
    }
})
