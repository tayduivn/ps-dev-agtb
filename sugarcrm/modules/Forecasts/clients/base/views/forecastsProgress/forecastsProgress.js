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
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.ProgressView
 * @alias SUGAR.App.layout.ProgressView
 * @extends View.View
 */
({

    likelyTotal: 0,
    bestTotal: 0,
    shouldRollup: 0,
    progressDataSet : [],
    /**
     * events on the view for which to watch
     */
    events : {
        'click #forecastsProgressDisplayOptions div.datasetOptions label.radio' : 'changeDisplayOptions'
    },

    /**
     * event handler to update which dataset is used.
     */
    changeDisplayOptions : function(evt) {
        evt.preventDefault();
        this.handleOptionChange(evt);
    },

    /**
     * Handle the click event for the optins menu
     *
     * @param evt
     * @return {Array}
     */
    handleOptionChange: function(evt) {
        el = $(evt.currentTarget);
        var changedSegment = el.attr('data-set');

        //check what needs to be done to the target
        if(el.hasClass('checked')) {
            //item was checked, uncheck it
            el.removeClass('checked');
            $('div .projected_' + changedSegment).hide();
        } else {
            //item was unchecked and needs checked now
            el.addClass('checked');
            $('div .projected_' + changedSegment).show();
        }
    },

    /**
     * initialize base models and set the initial user and timeperiod
     * @param options
     */
    initialize: function (options) {
        app.view.View.prototype.initialize.call(this, options);

        this.model = new Backbone.Model({
            opportunities : 0,
            revenue : 0,
            closed_amount : 0,
            closed_likely_amount : 0,
            closed_likely_percent : 0,
            closed_likely_above : 0,
            quota_amount : 0,
            quota_likely_amount : 0,
            quota_likely_percent : 0,
            quota_likely_above : 0,
            closed_best_amount : 0,
            closed_best_percent : 0,
            closed_best_above : 0,
            quota_best_amount : 0,
            quota_best_percent : 0,
            quota_best_above : 0,
            closed_worst_amount : 0,
            closed_worst_percent : 0,
            closed_worst_above : 0,
            quota_worst_amount : 0,
            quota_worst_percent : 0,
            quota_worst_above : 0,
            show_projected_likely: false,
            show_projected_best: false,
            show_projected_worst: false,
            pipeline : 0
        });

        this.selectedUser = this.context.get("selectedUser");
        this.shouldRollup = this.isManagerView();
        this.selectedTimePeriod = this.context.get("selectedTimePeriod");
        this.likelyTotal = 0;
        this.bestTotal = 0;
        this.worstTotal = 0;
        this.updateProgress();
    },

    /**
     * Clean up any left over bound data to our context
     */
    unbindData : function() {
        if(this.context) this.context.off(null, null, this);
        app.view.View.prototype.unbindData.call(this);
    },

    /**
     * bind to data changes in the context model.
     */
    bindDataChange: function () {

        var self = this;

        //render when model changes
        if(this.model) {
            this.model.on("change reset", self.render, this);
        }

        if (this.context) {
            //update user
            this.context.on("change:selectedUser reset:selectedUser",
            function(context, selectedUser) {
                this.updateProgressForSelectedUser(selectedUser);
                this.updateProgress();
            }, this);

            //commits could have changed quotas or any other number being used in the projected panel, do a fresh pull
            this.context.on("forecasts:committed:commit", function(context, flag) {
                    this.updateProgress();
            }, this);
            this.context.on("forecasts:worksheet:saved forecasts:committed:saved", function(){
                self.updateProgress();
            });


            //update timeperiod
            this.context.on("change:selectedTimePeriod reset:selectedTimePeriod",
            function(context, selectedTimePeriod) {
                this.updateProgressForSelectedTimePeriod(selectedTimePeriod);
                this.updateProgress();
            }, this);

            //Manager totals model has changed
            this.context.on("forecasts:worksheetManager:updateTotals", function(totals) {
                if(self.shouldRollup) {
                    self.recalculateManagerTotals(totals);
                }
            });
            //Rep totals model has changed
            this.context.on("change:updatedTotals", function(context, totals) {
                if(!self.shouldRollup) {
                    self.recalculateRepTotals(totals);
                }
            });
        }
    },

    /**
     * Handle putting the options into the values array that is used to keep track of what changes
     * so we only render when something changes.
     * @param options
     */
    handleRenderOptions:function () {
        this.model.set({
            show_projected_likely: _.has(this.progressDataSet, "likely"),
            show_projected_best: _.has(this.progressDataSet, "best"),
            show_projected_worst: _.has(this.progressDataSet, "worst")
        });
    },

    /**
     * take in the totals when they update for the rep worksheet and make sure the rest of the progress model recalculates according to the changes
     * @param totals model that was updated
     */
    recalculateRepTotals: function (totals) {
        this.likelyTotal = totals.amount;
        this.bestTotal = totals.best_case;
        this.worstTotal = totals.worst_case;

        var _model = {
            closed_amount: totals.won_amount,
            opportunities : 0,
            revenue : 0
        };

        if (app.user.get('id') != this.selectedUser.id) {
            _model.revenue = app.math.sub(totals.amount, totals.closed_amount);
            _model.opportunities = app.math.sub(totals.included_opp_count, totals.closed_count);
        } else {
            _model.revenue = app.math.sub(totals.overall_amount, app.math.add(totals.lost_amount, totals.won_amount));
            _model.opportunities = app.math.sub(totals.total_opp_count, app.math.add(totals.lost_count, totals.won_count));
        }

        this.model.set(_model);
        this.recalculateModel();
    },

    /**
     * take in the totals when they update for the manager worksheet and make sure the rest of the progress model recalculates according to the changes
     * @param totals model that was updated
     */
    recalculateManagerTotals: function (totals) {
        this.likelyTotal = totals.likely_adjusted;
        this.bestTotal = totals.best_adjusted;
        this.worstTotal = totals.worst_adjusted;
        this.recalculateModel();
    },

    recalculateModel: function () {
        this.model.set({
            closed_likely_amount : this.getAbsDifference(this.likelyTotal, this.model.get('closed_amount')),
            closed_likely_percent : this.getPercent(this.likelyTotal, this.model.get('closed_amount')),
            closed_likely_above : this.checkIsAbove(this.likelyTotal, this.model.get('closed_amount')),
            closed_best_amount : this.getAbsDifference(this.bestTotal, this.model.get('closed_amount')),
            closed_best_percent : this.getPercent(this.bestTotal, this.model.get('closed_amount')),
            closed_best_above : this.checkIsAbove(this.bestTotal, this.model.get('closed_amount')),
            closed_worst_amount : this.getAbsDifference(this.worstTotal, this.model.get('closed_amount')),
            closed_worst_percent : this.getPercent(this.worstTotal, this.model.get('closed_amount')),
            closed_worst_above : this.checkIsAbove(this.worstTotal, this.model.get('closed_amount')),
            quota_likely_amount : this.getAbsDifference(this.likelyTotal, this.model.get('quota_amount')),
            quota_likely_percent : this.getPercent(this.likelyTotal, this.model.get('quota_amount')),
            quota_likely_above : this.checkIsAbove(this.likelyTotal, this.model.get('quota_amount')),
            quota_best_amount : this.getAbsDifference(this.bestTotal, this.model.get('quota_amount')),
            quota_best_percent : this.getPercent(this.bestTotal, this.model.get('quota_amount')),
            quota_best_above : this.checkIsAbove(this.bestTotal, this.model.get('quota_amount')),
            quota_worst_amount : this.getAbsDifference(this.worstTotal, this.model.get('quota_amount')),
            quota_worst_percent : this.getPercent(this.worstTotal, this.model.get('quota_amount')),
            quota_worst_above : this.checkIsAbove(this.worstTotal, this.model.get('quota_amount')),
            pipeline : this.calculatePipelineSize(this.likelyTotal, this.model.get('revenue'))
        });
    },

    /**
     * determine if one value is bigger than another, used as a shortcut method to determine likely/best is above quota/closed
     * @param caseValue
     * @param stageValue
     * @return {Boolean}
     */
    checkIsAbove: function (caseValue, stageValue) {
        return caseValue > stageValue;
    },

    /**
     * return the difference of two values and make sure it's a positive value
     *
     * used as a shortcut function for determine best/likely to closed/quota
     * @param caseValue
     * @param stageValue
     * @return {Number}
     */
    getAbsDifference: function (caseValue, stageValue) {
        return Math.abs(stageValue - caseValue);
    },

    /**
     * return value to be used as a percent based on the two inputs, shortcut method for determining percentage to go or above
     * @param caseValue
     * @param stageValue
     * @return {Number}
     */
    getPercent: function (caseValue, stageValue) {
        return stageValue > 0 ? caseValue / stageValue : 0;
    },

    /**
     * calculates the pipeline size to one significant figure.  based on revenue with closed amount divided by the likely amount
     * @param likelyTotal
     * @param revenue
     * @param closed
     * @return {Number}
     */
    calculatePipelineSize: function (likelyTotal, revenue) {
        var ps = 0;
        if ( likelyTotal > 0 ) {
            ps = revenue /  likelyTotal;

            // Round to 1 decimal place
            ps = Math.round( ps * 10 )/10;
        }

        // This value is used in the template.
        return ps;
    },

    /**
     * checks the selectedUser to make sure it's a manager and if we should show the manager view
     * @return {Boolean}
     */
    isManagerView: function () {
        return this.selectedUser.isManager === true && (this.selectedUser.showOpps == undefined || this.selectedUser.showOpps === false);
    },


    _renderHtml: function (ctx, options) {
        _.extend(this, this.model.toJSON());
        this.progressDataSet = app.utils.getAppConfigDatasets('forecasts_options_dataset', 'show_worksheet_', this.context.config);
        this.handleRenderOptions();

        app.view.View.prototype._renderHtml.call(this, ctx, options);
    },

    /**
     * set the new time period
     * @param selectedTimePeriod
     */
    updateProgressForSelectedTimePeriod: function (selectedTimePeriod) {
        this.selectedTimePeriod = selectedTimePeriod;
    },

    /**
     * set the new selected user
     * @param selectedUser
     */
    updateProgressForSelectedUser: function (selectedUser) {
        this.selectedUser = selectedUser;
        this.shouldRollup = this.isManagerView();
    },

    /**
     * something has changed, so we need to update the progress model depending on this change
     */
    updateProgress: function () {
        var self = this;

        var method = self.shouldRollup ? "progressManager" : "progressRep";

        var url = 'Forecasts/' + self.selectedTimePeriod.id + '/' + method + '/' + self.selectedUser.id;
        url = app.api.buildURL(url);

        app.api.call('read', url, null, null, {
            success: function(data) {
                if(self.shouldRollup) {
                    self.model.set({
                        opportunities : data.opportunities,
                        closed_amount : data.closed_amount,
                        revenue : data.pipeline_revenue,
                        quota_amount : data.quota_amount
                    });
                } else {
                    self.model.set({
                        quota_amount : data.quota_amount
                    });
                }
                self.recalculateModel();
            }
        });
    }
})
