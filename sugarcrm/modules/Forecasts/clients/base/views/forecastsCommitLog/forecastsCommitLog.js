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
 * View that displays committed forecasts for current user.  If the manager view is selected, the Forecasts
 * of Rollup type are shown; otherwise the Forecasts of Direct type are shown.
 *
 * @class View.Views.GridView
 * @alias SUGAR.App.layout.GridView
 * @extends View.View
 */
({
    /**
     * The class selector representing the element which contains the view output
     */
    viewSelector: '.forecastsCommitted',

    /**
     * Stores the best case to display in the view
     */
    bestCase: '',

    /**
     * Stores the likely case to display in the view
     */
    likelyCase: '',

    /**
     * Stores the worst case to display in the view
     */
    worstCase: '',

    /**
     * Used to query for the forecast_type value in Forecasts
     */
    forecastType: 'Direct',

    /**
     * Stores the historical log of the Forecast entries
     */
    commitLog: [],

    /**
     * Previous committed date value to display in the view
     */
    previousDateEntered: '',

    events : {
        'click i[id=show_hide_history_log]' : 'showHideHistoryLog'
    },

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        this.resetCommittedLog();
    },

    /**
     * Switch showHistoryLog flag for expanding/collapsing log after commit
     */
    showHideHistoryLog: function() {
        this.$el.find('i[id=show_hide_history_log]').toggleClass('icon-caret-down icon-caret-up');
        this.$el.find('div[id=history_log_results]').toggleClass('hide');
    },

    bindDataChange: function() {
        if(this.context) {
            // the only thing CommitLog cares about is when committed's collection changes
            this.context.on('forecasts:committed:collectionUpdated', function(collection) {
                this.collection.reset(collection.toJSON());
                this.buildCommitLog()
            }, this);
        }
    },

    /**
     * Utility method to reset the committed log in the event that no models are returned for the
     * selected user/timeperiod
     */
    resetCommittedLog: function() {
        this.bestCase = "";
        this.likelyCase = "";
        this.worstCase = "";
        this.previousDateEntered = "";
    },

    /**
     * Does the heavy lifting of looping through models to build the commit history
     */
    buildCommitLog: function() {
        //Reset the history log
        this.commitLog = [];

        // if we have no models, exit out of the method
        if(_.isEmpty(this.collection.models)) {
            this.resetCommittedLog();
            if (!this.disposed) {
                this.render();
            }
            return;
        }

        // get the first model so we can get the previous date entered
        var previousModel = _.first(this.collection.models);

        // parse out the previous date entered
        var dateEntered = new Date(Date.parse(previousModel.get('date_entered')));
        if(dateEntered == 'Invalid Date') {
            dateEntered = previousModel.get('date_entered');
        }
        // set the previous date entered in the users format
        this.previousDateEntered = app.date.format(dateEntered, app.user.getPreference('datepref') + ' ' + app.user.getPreference('timepref'));

        //loop through from oldest to newest to build the log correctly
        var loopPreviousModel = '';
        var models = _.clone(this.collection.models).reverse();
        _.each(models, function(model) {
            this.commitLog.push(app.utils.createHistoryLog(loopPreviousModel, model));
            loopPreviousModel = model;
        }, this);

        //reset the order of the history log for display
        this.commitLog.reverse();

        if (!this.disposed) {
            this.render();
        }
    }
})
