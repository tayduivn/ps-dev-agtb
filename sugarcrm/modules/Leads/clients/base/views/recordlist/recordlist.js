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
    extendsFrom: 'RecordlistView',

    initialize: function(options) {
        app.view.invoke(this, 'view', 'recordlist', 'initialize', {args:[options]});
        this.context.on('list:convertrow:fire', this.initiateDrawer, this);
    },

    /**
     * Set the save button to show if the model has been edited.
     */
    bindDataChange: function() {
        app.view.invoke(this, 'view', 'recordlist', 'bindDataChange');
        this.collection.on('reset', this.setLeadButtonStates, this);
    },

    /**
     * Hide any convert row actions for leads that are already converted
     */
    setLeadButtonStates: function() {
        _.each(this.fields, function(field) {
            if (field.name === 'lead_convert_button' && (field.model.get('converted') === true)) {
                field.hide();
            }
        });
    },

    /**
     * Event to trigger the convert lead process for the lead
     */
    initiateDrawer: function(selectedModel) {
        var model = app.data.createBean(this.model.module);
        model.copy(selectedModel);
        model.set('id', selectedModel.id);

        app.drawer.open({
            layout : "convert",
            context: {
                forceNew: true,
                module: 'Leads',
                leadsModel: model
            }
        });
    }
})
