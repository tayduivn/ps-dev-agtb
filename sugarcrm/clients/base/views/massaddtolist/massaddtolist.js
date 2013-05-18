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
     * @class View.MassaddtolistView
     * @alias SUGAR.App.view.views.MassaddtolistView
     * @extends View.MassupdateView
     */
    extendsFrom: 'MassupdateView',
    addToListFieldName: 'prospect_lists',

    /**
     * Listen for just the massaddtolist event from the list view
     */
    delegateListFireEvents: function() {
        this.layout.on("list:massaddtolist:fire", this.show, this);
        this.layout.on("list:massaction:hide", this.hide, this);
    },

    /**
     * Pull out the target list link field from the field list and treat it like a relate field for later rendering
     * @param options
     */
    setMetadata: function(options) {
        var moduleMetadata = app.metadata.getModule(options.module);

        var addToListField = _.find(moduleMetadata.fields, function(field) {
            return field.name === this.addToListFieldName;
        }, this);

        if (addToListField) {
            addToListField = app.utils.deepCopy(addToListField);
            addToListField.id_name = this.addToListFieldName + '_id';
            addToListField.name = this.addToListFieldName + '_name';
            addToListField.label = addToListField.label || addToListField.vname;
            addToListField.type = 'relate';
            this.addToListField = addToListField;
        }
    },

    /**
     * Hide the view if we were not able to find the appropriate list field and somehow render is triggered
     */
    _render: function() {
        var result = app.view.invokeParent(this, {type: 'view', name: 'massupdate', method: '_render'});

        if(_.isUndefined(this.addToListField)) {
            this.hide();
        }
        return result;
    },

    /**
     * There is only one field for this view, so it is the default as well
     */
    setDefault: function() {
        this.defaultOption = this.addToListField;
    },

    /**
     * When adding to a target list, the API is expecting an array of IDs
     */
    getAttributes: function() {
        var attributes = {};
        attributes[this.addToListFieldName] = [
            this.model.get(this.addToListFieldName + '_id')
        ];
        return attributes;
    }
})
