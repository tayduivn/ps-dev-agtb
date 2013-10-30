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
     * {@inheritdocs}
     */
    initialize: function(options) {
        var acls = app.user.getAcls().Forecasts,
            hasAccess = (!_.has(acls, 'access') || acls.access == 'yes'),
            isSysAdmin = (app.user.get('type') == 'admin'),
            isDev = (!_.has(acls, 'developer') || acls.developer == 'yes');
        // if user has access AND is a System Admin OR has a Developer role
        if(hasAccess && (isSysAdmin || isDev)) {
            // initialize
            app.view.Layout.prototype.initialize.call(this, options);
            // load the data
            app.view.Layout.prototype.loadData.call(this);
        } else {
            this.codeBlockForecasts('LBL_FORECASTS_NO_ACCESS_TO_CFG_TITLE', 'LBL_FORECASTS_NO_ACCESS_TO_CFG_MSG');
        }
    },

    /**
     * Blocks forecasts from continuing to load
     */
    codeBlockForecasts: function(title, msg) {
        var alert = app.alert.show('no_access_to_forecasts', {
            level: 'error',
            autoClose: false,
            title: app.lang.get(title, "Forecasts") + ":",
            messages: [app.lang.get(msg, "Forecasts")]
        });

        alert.getCloseSelector().on('click', function() {
            alert.getCloseSelector().off();
            app.router.navigate('#Home', {trigger: true});
        });
    },

    /**
     * Overrides loadData to defer it running until we call it in _onceInitSelectedUser
     *
     * @override
     */
    loadData: function() {
    }
})
