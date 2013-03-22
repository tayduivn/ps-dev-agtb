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
 *
 *
 * Events Triggered
 *
 * forecasts:commitButtons:enabled
 *      on: context
 *      by: updateTotals()
 *
 * forecasts:commitButtons:disabled
 *      on: context
 *      by: commitForecast()
 *
 * forecasts:committed:saved
 *      on: context
 *      by: commitForecast()
 *      when: the new forecast model has saved successfully
 */


({
    /**
     * The url for the REST endpoint
     */
    url: 'rest/v10/Forecasts',

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
     * Stores the likely case to display in the view
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
     * Stores the Forecast totals to use when creating a new entry
     */
    totals: null,

    /**
     * Stores the previous totals to display in the view
     */
    previousTotals: null,

    /**
     * Template to use when updating the bestCase on the committed bar
     */
    bestTemplate: _.template('<%= bestCase %>&nbsp;<span class="icon-sm committed_arrow<%= bestCaseCls %>"></span>'),

    /**
     * Template to use when updating the likelyCase on the committed bar
     */
    likelyTemplate: _.template('<%= likelyCase %>&nbsp;<span class="icon-sm committed_arrow<%= likelyCaseCls %>"></span>'),

    /**
     * Template to use when updating the worstCase on the committed bar
     */
    worstTemplate: _.template('<%= worstCase %>&nbsp;<span class="icon-sm committed_arrow<%= worstCaseCls %>"></span>'),

    savedTotal: null,

    runningFetch: false,

    /**
     * the timeperiod field metadata that gets used at render time
     */
    timeperiod: {},

    /**
     * Show The Likely Box
     */
    show_likely: true,

    /**
     * Show The Best Box
     */
    show_best: false,

    /**
     * Show This Wost Box
     */
    show_worst: false,

    /**
     * Does the layout have the forecastCommitLog View?
     */
    layoutHasForecastCommitLog: true,

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        this.bestCase = 0;
        this.likelyCase = 0;
        this.worstCase = 0;

        this.show_likely = app.metadata.getModule('Forecasts', 'config').show_worksheet_likely;
        this.show_best = app.metadata.getModule('Forecasts', 'config').show_worksheet_best;
        this.show_worst = app.metadata.getModule('Forecasts', 'config').show_worksheet_worst;

        this.selectedUser = {id: app.user.get('id'), "isManager": app.user.get('isManager'), "showOpps": false};

        if(_.isUndefined(this.layout.getComponent('forecastsCommitLog'))) {
            this.layoutHasForecastCommitLog = false;
            this.forecastType = (app.user.get('isManager') == true && app.user.get('showOpps') == false) ? 'Rollup' : 'Direct';
            this.timePeriod = app.defaultSelections.timeperiod_id.id;

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

    updateCommitted: function() {
        this.runningFetch = true;
        this.bestCase = 0;
        this.likelyCase = 0;
        this.worstCase = 0;
        this.likelyCaseCls = '';
        this.bestCaseCls = '';
        this.worstCaseCls = '';
        this.totals = null;

        if(!this.layoutHasForecastCommitLog) {
            this.context.resetLoadFlag();
            this.loadData();
        }
    },

    /**
     * Clean up any left over bound data to our context
     */
    unbindData: function() {
        if(this.context) this.context.off(null, null, this);
        app.view.View.prototype.unbindData.call(this);
    },

    bindDataChange: function() {

        this.collection.on("reset", function() {
            this.runningFetch = false;
            if(!_.isEmpty(this.savedTotal)) {
                this.updateTotals(this.savedTotal);
            }
        }, this);
        this.collection.on('data:sync:start', function() {
            // when a request start up, tell the class that the fetch is running
            this.runningFetch = true;
        }, this);

        if(this.context) {
            this.context.on("change:selectedUser", function(context, user) {
                this.forecastType = user.showOpps ? 'Direct' : 'Rollup';
                this.selectedUser = user;
                this.updateCommitted();
            }, this);
            this.context.on("change:selectedTimePeriod", function(context, timePeriod) {
                this.timePeriod = timePeriod.id;
                this.updateCommitted();
            }, this);
            this.context.on("change:updatedTotals", function(context, totals) {
                if(this.selectedUser.isManager == true && this.selectedUser.showOpps == false) {
                    return;
                }
                this.updateTotals(totals);
            }, this);
            this.context.on("forecasts:worksheetManager:updateTotals", function(totals) {
                if(this.selectedUser.isManager == true && this.selectedUser.showOpps == false) {
                    this.updateTotals(totals);
                }
            }, this);
            this.context.on("forecasts:committed:commit", function(context, flag) {
                this.commitForecast();
            }, this);
        }
    },

    /**
     * Common code to update the totals
     *
     * @param totals
     */
    updateTotals: function(totals) {
        // we need to clone this to not affect other views
        var _totals = _.clone(totals);


        // these fields don't matter when it comes to tracking these values so just 0 them out.
        // we don't care about this field
        if(!_.isUndefined(_totals.quota)) {
            _totals.quota = 0;
        }

        if(!_.isEqual(this.totals, _totals)) {
            var best = {};
            var likely = {};
            var worst = {};
            // get the last committed value
            var previousCommit = null;
            if(!_.isEmpty(this.collection.models)) {
                previousCommit = _.first(this.collection.models);
            } else {
                previousCommit = new Backbone.Model({
                    best_case: 0,
                    likely_case: 0,
                    worst_case: 0
                });
            }

            if(this.runningFetch == true) {
                this.savedTotal = _totals;
                return;
            } else if(!_.isEmpty(this.savedTotal)) {
                //This line is needed since we need to clean up savedTotals if it has something and you are processing a set of totals.
                //The reason for this is that the method gets called again once the reset is done on the collection if one is ran.
                this.savedTotal = null;
            }

            if(this.selectedUser.isManager == true && this.selectedUser.showOpps === false) {
                // management view
                best.bestCaseCls = this.getColorArrow(_totals.best_adjusted, previousCommit.get('best_case'));
                best.bestCase = app.currency.formatAmountLocale(_totals.best_adjusted);
                likely.likelyCaseCls = this.getColorArrow(_totals.likely_adjusted, previousCommit.get('likely_case'));
                likely.likelyCase = app.currency.formatAmountLocale(_totals.likely_adjusted);
                worst.worstCaseCls = this.getColorArrow(_totals.worst_adjusted, previousCommit.get('worst_case'));
                worst.worstCase = app.currency.formatAmountLocale(_totals.worst_adjusted);
            } else {
                // sales rep view
                best.bestCaseCls = this.getColorArrow(_totals.best_case, previousCommit.get('best_case'));
                best.bestCase = app.currency.formatAmountLocale(_totals.best_case);
                likely.likelyCaseCls = this.getColorArrow(_totals.amount, previousCommit.get('likely_case'));
                likely.likelyCase = app.currency.formatAmountLocale(_totals.amount);
                worst.worstCaseCls = this.getColorArrow(_totals.worst_case, previousCommit.get('worst_case'));
                worst.worstCase = app.currency.formatAmountLocale(_totals.worst_case);
            }

            if(!_.isEmpty(best.bestCaseCls) || !_.isEmpty(likely.likelyCaseCls) || !_.isEmpty(worst.worstCaseCls)) {
                this.context.trigger("forecasts:commitButtons:enabled");
            }

            this.bestCaseCls = best.bestCaseCls;
            this.bestCase = best.bestCase;
            this.likelyCaseCls = likely.likelyCaseCls;
            this.likelyCase = likely.likelyCase;
            this.worstCaseCls = worst.worstCaseCls;
            this.worstCase = worst.worstCase;

            $('h2#best').html(this.bestTemplate(best));
            $('h2#likely').html(this.likelyTemplate(likely));
            $('h2#worst').html(this.worstTemplate(worst));

        }

        this.totals = _totals;
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
     * commit the forecast and by creating a forecast entry if the totals have been updated and the new forecast entry
     * is different from the previous one (best_case and likely_case are not exactly identical)
     *
     */
    commitForecast: function() {

        this.context.trigger("forecasts:commitButtons:disabled");

        //If the totals have not been set, don't save
        if(!this.totals) {
            return;
        }

        var forecast = new this.collection.model();
        forecast.url = this.url;

        var forecastData = {};

        if(this.selectedUser.isManager == true && this.selectedUser.showOpps == false) {
            forecastData.best_case = this.totals.best_adjusted;
            forecastData.likely_case = this.totals.likely_adjusted;
            forecastData.worst_case = this.totals.worst_adjusted;
        } else {
            forecastData.best_case = this.totals.best_case;
            forecastData.likely_case = this.totals.amount;
            forecastData.worst_case = this.totals.worst_case;
        }

        // we need a commit_type so we know what to do on the back end.
        forecastData.commit_type = (this.context.get('currentWorksheet') == "worksheetmanager") ? 'manager' : 'sales_rep';
        forecastData.timeperiod_id = this.timePeriod;
        forecastData.forecast_type = this.forecastType;
        forecastData.amount = this.totals.amount;
        forecastData.opp_count = this.totals.included_opp_count;
        forecastData.includedClosedAmount = this.totals.includedClosedAmount;
        forecastData.includedClosedCount = this.totals.includedClosedCount;
        forecastData.pipeline_amount = this.totals.pipeline_amount;
        forecastData.pipeline_opp_count = this.totals.pipeline_opp_count;

        // apply data to model then save
        forecast.save(forecastData, { success: _.bind(function() {
            this.context.trigger("forecasts:committed:saved");
        }, this), silent: true});

        // clear out the arrows
        this.likelyCaseCls = '';
        this.bestCaseCls = '';
        this.worstCaseCls = '';

        this.previous = this.totals;
        this.collection.unshift(forecast);
    }
})
