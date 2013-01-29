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
     * Commit Date
     */
    commitDate: '',

    /**
     * Deferred object for manager worksheet render
     */
    mDeferred: $.Deferred(),

    /**
     * Deferred object for worksheet model being ready
     */
    wDeferred:$.Deferred(),

    bindDataChange: function() {
        var self = this;

        if(self.context && self.context.forecasts) {
            //Bind to the worksheetmanager render event so we know that the view has been rendered
            self.context.forecasts.on("forecasts:worksheetmanager:rendered", function() {
                self.mDeferred.resolve();
            });
            //Bind to the committed model being reset so we know that the model has been updated
            self.context.forecasts.committed.on("reset", function() {
                self.wDeferred.resolve();
            });
        }

        self.handleDeferredRender();
    },

    /**
     * Handles setting up the listeners for the two deferred objects.  When both conditions are satisfied
     * it calls _render and sets itself up again.
     *
     */
    handleDeferredRender: function() {
        var self = this;
        $.when(self.wDeferred, self.mDeferred).done(function() {
            self._render();
            //Reset the deferred objects
            self.wDeferred = self.mDeferred = $.Deferred();
            self.handleDeferredRender();
        });
    },

    /**
     * Overwrite the render method.  This function also does some checks to determine whether or not to show an
     * alert indicating a commit entry.  The alert is shown for a reportee if
     *
     * 1) The reportee's forecast was commit at a time after the most recent manager's forecast commit
     * or
     * 2) If the manager had no history of a forecast commit
     *
     * @return {*}
     * @private
     */
    _render:function () {
        if(this.context) {
            this.showFieldAlert = false;
            this.uid = this.model.get('user_id');
            this.showOpps = this.model.get('show_opps');

            var fieldDate;

            if(this.model.get('date_modified')) {
               fieldDate = new Date(this.model.get('date_modified'));
            }
            
            if(!_.isEmpty(this.context.forecasts.committed.models)) {
                var lastCommittedDate = new Date(_.first(this.context.forecasts.committed.models).get('date_modified'));

                // if fieldDate is newer than the forecast commitDate value, then we want to show the field
                if (_.isDate(fieldDate) && _.isDate(lastCommittedDate)) {
                    this.showFieldAlert = (fieldDate.getTime() > lastCommittedDate.getTime());
                }
            } else if(_.isDate(fieldDate)) {
                this.showFieldAlert = true;
            }

            this.commitDate = fieldDate;
            this.options.viewName = 'historyLog';
            app.view.Field.prototype._render.call(this);
        }
        return this;
    }

})
