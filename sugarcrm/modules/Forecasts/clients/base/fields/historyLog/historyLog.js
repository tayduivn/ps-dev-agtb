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
     * Do we show this field alert
     */
    showFieldAlert: false,

    /**
     * The User Id
     */
    uid: '',

    /**
     * Show Opportunities - this param gets set on the model as a way to flag the data:
     * TRUE - rep worksheet link or manager's Opportunities link (forecast_type: direct)
     * FALSE - link to manager worksheet (forecast_type: rollup)
     */
    showOpps: '',

    /**
     * if the user is a manager or not
     */
    isManager: 0,

    initialize : function(options) {
        app.view.Field.prototype.initialize.call(this, options);

        this.showFieldAlert = (this.model.get('show_history_log') == "1");

        // if we're not showing the field, no need to do anything else
        if(this.showFieldAlert) {
            if(this.model.get('date_modified')) {
                this.commitDate = new Date(this.model.get('date_modified'));
            }

            this.uid = this.model.get('user_id');

            // Have to make it 1 or 0 for handlebars to parse properly
            this.showOpps = (this.context.get('selectedUser').id == this.uid) ? 1 : 0;
            this.isManager = (this.model.get('isManager')) ? 1 : 0;
        }
    }
})
