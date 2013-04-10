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
    extendsFrom: 'RecordView',

    delegateButtonEvents: function() {
        this.context.on('button:lead_convert_button:click', this.initiateDrawer, this);
        this.context.on('button:manage_subscriptions:click', this.manageSubscriptionsClicked, this);
        app.view.views.RecordView.prototype.delegateButtonEvents.call(this);
    },

    /**
     * Set the save button to show if the model has been edited.
     */
    bindDataChange: function() {
        app.view.views.RecordView.prototype.bindDataChange.call(this);
        this.model.on('change', this.setLeadButtonStates, this);
    },

     /**
     * Change the behavior of buttons depending on the state that they should be in
     */
    setLeadButtonStates: function() {
        var convertButton = this.getField('lead_convert_button'),
            convertedState = this.model.get('converted');

        if(convertedState) {
            convertButton.hide();
        }
    },

    /**
     * Event to trigger the convert lead process for the lead
     */
    initiateDrawer: function() {
        var model = app.data.createBean(this.model.module);
        model.copy(this.model);
        model.set('id', this.model.id);

        app.drawer.open({
            layout : "convert",
            context: {
                forceNew: true,
                module: 'Leads',
                leadsModel: model
            }
        });
    },

    /**
     * Event to trigger the Manage Subscriptions for the lead
     */
    manageSubscriptionsClicked: function() {
        var params = [
            {'name': 'sidecar_return', value: app.router.buildRoute(this.module, this.model.id)},
            {'name': 'return_module', value: this.module},
            {'name': 'record', value: this.model.id},
            {'name': 'action', value: 'Subscriptions'},
            {'name': 'module', value: 'Campaigns'}
        ];

        var route = '#bwc/index.php?' + $.param(params);
        app.router.navigate(route, {trigger: true});
    },

    /**
     * Remove id, status and converted fields (including associations created during conversion) when duplicating a Lead
     * @param prefill
     */
    setupDuplicateFields: function(prefill){
        var duplicateBlackList = ["id", "status", "converted", "account_id", "opportunity_id", "contact_id"];
        _.each(duplicateBlackList, function(field){
            if(field && prefill.has(field)){
                prefill.unset(field);
            }
        });
    }
})
