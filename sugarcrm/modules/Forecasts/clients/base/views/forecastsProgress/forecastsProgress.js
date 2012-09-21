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
    /**
     * initialize base models and set the initial user and timeperiod
     * @param options
     */
    initialize: function (options) {
        _.bindAll(this); // Don't want to worry about keeping track of "this"
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

                    pipeline : 0
                });

        this.selectedUser = this.context.forecasts.get("selectedUser");
        this.shouldRollup = this.isManagerView();
        this.selectedTimePeriod = this.context.forecasts.get("selectedTimePeriod");
        this.likelyTotal = 0;
        this.bestTotal = 0;
        this.worstTotal = 0;
        this.updateProgress();
    },

    /**
     * bind to data changes in teh context model.
     */
    bindDataChange: function () {

        var self = this;

        //render when model changes
        if(this.model) {
            this.model.on("change reset", this.render, this);
        }

        if (this.context.forecasts) {
            //update uer
            this.context.forecasts.on("change:selectedUser reset:selectedUser",
            function(context, selectedUser) {
                this.updateProgressForSelectedUser(selectedUser);
                this.updateProgress();
            }, this);

            //update timeperiod
            this.context.forecasts.on("change:selectedTimePeriod reset:selectedTimePeriod",
            function(context, selectedTimePeriod) {
                this.updateProgressForSelectedTimePeriod(selectedTimePeriod);
                this.updateProgress();
            }, this);

            //Manager totals model has changed
            this.context.forecasts.on("change:updatedManagerTotals", function(context, totals) {
                if(self.shouldRollup) {
                    self.recalculateManagerTotals(totals);
                }
            });
            //Rep totals model has changed
            this.context.forecasts.on("change:updatedTotals", function(context, totals) {
                if(!self.shouldRollup) {
                    self.recalculateRepTotals(totals);
                }
            });
        }
    },


    /**
     * take in the totals when they update for the rep worksheet and make sure the rest of the progress model recalculates according to the changes
     * @param totals model that was updated
     */
    recalculateRepTotals: function (totals) {
        this.likelyTotal = totals.amount;
        this.bestTotal = totals.best_case;
        this.worstTotal = totals.worst_case;
        this.model.set({
            closed_amount : totals.won_amount,
            opportunities : totals.total_opp_count - totals.lost_count - totals.won_count,
            revenue : totals.overall_amount - totals.lost_amount - totals.won_amount
        });
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
        this.model.set({
            quota_amount : totals.quota
        });
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

       var urlParams = {
            user_id: self.selectedUser.id,
            timeperiod_id : self.selectedTimePeriod.id
        };
       var url = app.api.buildURL('Forecasts', method, '', urlParams);


        app.api.call('read', url, null, null, {
            success: function(data) {
                if(self.shouldRollup) {
                    self.model.set({
                        opportunities : data.opportunities,
                        closed_amount : data.closed_amount,
                        revenue : data.pipeline_revenue
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
