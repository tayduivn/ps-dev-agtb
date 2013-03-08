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
    bestCase: 0,

    /**
     * Stores the likely case to display in the view
     */
    likelyCase: 0,

    /**
     * Stores the worst case to display in the view
     */
    worstCase: 0,


    /**
     * Used to query for the timeperiod_id value in Forecasts
     */
    timePeriod: '',

    /**
     * Used to query for the forecast_type value in Forecasts
     */
    forecastType: 'Direct',

    /**
     * Stores the historical log of the Forecast entries
     */
    historyLog: [],

    /**
     * Store the Best Case Number from the very last commit in the log
     */
    previousBestCase: '',
    /**
     * Store the Likely Case Number from the very last commit in the log
     */
    previousLikelyCase: '',
    /**
     * Store the Worst Case Number from the very last commit in the log
     */
    previousWorstCase: '',

    /**
     * Does the layout have the forecastCommitted View?
     */
    layoutHasForecastCommitted: true,

    events : {
        'click i[id=show_hide_history_log]' : 'showHideHistoryLog'
    },

    initialize: function(options) {

        app.view.View.prototype.initialize.call(this, options);

        if(_.isUndefined(this.layout.getComponent('forecastsCommitted'))) {
            this.layoutHasForecastCommitted = false;
            this.forecastType = (app.user.get('isManager') == true && app.user.get('showOpps') == false) ? 'Rollup' : 'Direct';
            this.timePeriod = app.defaultSelections.timeperiod_id.id;
            this.selectedUser = {id: app.user.get('id'), "isManager": app.user.get('isManager'), "showOpps": false};

            this.bestCase = "";
            this.likelyCase = "";
            this.worstCase = "";

            // we have to override sync right now as there is no way to run the filter by default
            this.collection.sync = _.bind(function(method, model, options) {
                options.success = _.bind(function(resp, status, xhr) {
                    this.collection.reset(resp.records);
                }, this);
                // we need to force a post, so get the url object and put it in
                var url = this.createURL();
                app.api.call("create", url.url, url.filters, options);
            }, this);
        }
    },

    /**
     *
     * @return {object}
     */
    createURL: function() {
        // we need to default the type to products
        var args_filter = [];
        if(this.timePeriod) {
            args_filter.push({"timeperiod_id": this.timePeriod});
        }

        if(this.selectedUser) {
            args_filter.push({"user_id": this.selectedUser.id});
        }

        args_filter.push({"forecast_type": this.forecastType});

        var url = app.api.buildURL('Forecasts', 'filter');

        return {"url": url, "filters": {"filter": args_filter}};
    },

    /**
     * Switch showHistoryLog flag for expanding/collapsing log after commit
     */
    showHideHistoryLog: function() {
        this.$el.find('i[id=show_hide_history_log]').toggleClass('icon-caret-down icon-caret-up');
        this.$el.find('div[id=history_log_results]').toggleClass('hide');

    },

    /**
     * Renders the component
     */
    _renderHtml: function(ctx, options) {
        app.view.View.prototype._renderHtml.call(this, ctx, options);

        this.$el.parents('div.topline').find("span.lastBestCommit").html(this.previousBestCase);
        this.$el.parents('div.topline').find("span.lastLikelyCommit").html(this.previousLikelyCase);
        this.$el.parents('div.topline').find("span.lastWorstCommit").html(this.previousWorstCase);
    },


    bindDataChange: function() {
        if(this.collection) {
            this.collection.on('reset change', function() {
                this.buildForecastsCommitted();
            }, this);
        }

        // only add these handlers if the layout doesn't contain the forecastsCommittedLayout
        if(this.context && !this.layoutHasForecastCommitted) {
            this.context.on("change:selectedUser", function(context, user) {
                this.forecastType = user.showOpps ? 'Direct' : 'Rollup';
                this.selectedUser = user;
                this.context.resetLoadFlag();
                this.loadData();
            }, this);
            this.context.on("change:selectedTimePeriod", function(context, timePeriod) {
                this.timePeriod = timePeriod.id;
                this.context.resetLoadFlag();
                this.loadData();
            }, this);
        }
    },

    /**
     * Utility method to get the arrow and color depending on how the values match up.
     *
     * @param newValue
     * @param currentValue
     * @return {String}
     */
    getColorArrow: function(newValue, currentValue) {
        var cls = (newValue > currentValue) ? ' icon-arrow-up font-green' : ' icon-arrow-down font-red';
        return (newValue == currentValue) ? '' : cls;
    },

    /**
     * Utility method to reset the committed log in the event that no models are returned for the
     * selected user/timeperiod
     */
    resetCommittedLog: function() {
        this.bestCase = "";
        this.likelyCase = "";
        this.worstCase = "";
        this.previousBestCase = "";
        this.previousLikelyCase = "";
        this.previousWorstCase = "";
        this.previousDateEntered = "";
    },

    buildForecastsCommitted: function() {
        var previousModel;

        //Reset the history log
        this.historyLog = [];

        // if we have no models, exit out of the method
        if(_.isEmpty(this.collection.models)) {
            this.resetCommittedLog();
            if (!this.disposed) {
                this.render();
            }
            return;
        }

        // get the first model so we can get the previous date entered
        previousModel = _.first(this.collection.models);

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
            this.historyLog.push(app.utils.createHistoryLog(loopPreviousModel, model));
            loopPreviousModel = model;
        }, this);

        //reset the order of the history log for display
        this.historyLog.reverse();

        // save the values from the last model to display in the dataset line on the interface
        this.previousBestCase = app.currency.formatAmountLocale(previousModel.get('best_case'));
        this.previousLikelyCase = app.currency.formatAmountLocale(previousModel.get('likely_case'));
        this.previousWorstCase = app.currency.formatAmountLocale(previousModel.get('worst_case'));

        if (!this.disposed) {
            this.render();
        }
    }
})
