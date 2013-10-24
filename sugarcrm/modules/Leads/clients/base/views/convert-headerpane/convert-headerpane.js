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
    extendsFrom: 'HeaderpaneView',

    events:{
        'click [name=save_button]:not(".disabled")': 'initiateSave',
        'click [name=cancel_button]': 'initiateCancel'
    },

    initialize: function(options) {
        app.view.invokeParent(this, {type: 'view', name: 'headerpane', method: 'initialize', args: [options]});
        this.context.on('lead:convert-save:toggle', this.toggleSaveButton, this);
    },

    /**
     * Grab the lead's name and set the title to Convert: Name
     */
    _renderHtml: function() {
        var leadsModel = this.context.get('leadsModel');
        var name = !_.isUndefined(leadsModel.get('name')) ? leadsModel.get('name') : leadsModel.get('first_name') + ' ' + leadsModel.get('last_name');
        this.title = app.lang.get("LBL_CONVERTLEAD_TITLE", this.module) + ': ' + name;
        app.view.invokeParent(this, {type: 'view', name: 'headerpane', method: '_renderHtml'});
    },

    /**
     * When finish button is clicked, send this event down to the convert layout to wrap up
     */
    initiateSave: function() {
        this.context.trigger('lead:convert:save');
    },

    /**
     * When cancel clicked, hide the drawer
     */
    initiateCancel : function() {
        app.drawer.close();
    },

    /**
     * Enable/disable the Save button
     *
     * @param enable true to enable, false to disable
     */
    toggleSaveButton: function(enable) {
        this.$('[name=save_button]').toggleClass('disabled', !enable);
    }
})
