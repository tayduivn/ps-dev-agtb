/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.FilterView
 * @alias SUGAR.App.layout.FilterView
 * @extends View.View
 */
({

    likelyTotal: 0,
    bestTotal: 0,
    progressEndpoint:'',
    /**
     * initialize base models and set the initial user and timeperiod
     * @param options
     */
    initialize: function (options) {
        _.bindAll(this); // Don't want to worry about keeping track of "this"
        // CSS className must be changed to avoid conflict with Bootstrap CSS.
        options.className = "progressBar";
        app.view.View.prototype.initialize.call(this, options);
        this.progressEndpoint = app.api.serverUrl + "/Forecasts/progress/"

        this.model = new Backbone.Model({
                    amount : 0,
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
                    pipeline : 0
                });

        //this.progressModel = this.context.forecasts.progress;
        this.selectedUser = this.context.forecasts.get("selectedUser");
        this.selectedTimePeriod = this.context.forecasts.get("selectedTimePeriod");
        this.updateProgress();
        this.likelyTotal = 0;
        this.bestTotal = 0;
        this.worksheetManagerCollection = this.context.forecasts.worksheetmanager;
    },

    /**
     * bind to data changes in teh context model.
     */
    bindDataChange: function () {

        var self = this;

        if (this.context.forecasts) {
            this.context.forecasts.on("change:selectedUser",
            function(context, selectedUser) {
                this.updateProgressForSelectedUser(selectedUser);
                this.updateProgress();
            }, this);

            this.context.forecasts.on("change:selectedTimePeriod",
            function(context, selectedTimePeriod) {
                this.updateProgressForSelectedTimePeriod(selectedTimePeriod);
                this.updateProgress();
            }, this);
            this.context.forecasts.on("change:updatedTotals", function(context, totals) {
                self.calculateBases(totals);
                self.recalculateTotals(totals);
            });
            this.context.forecasts.on("change:updatedManagerTotals", function(context, totals) {
                self.calculateBases(totals);
                self.recalculateTotals(totals);
            });
        }
    },

    /**
     * update the base numbers used in almost every calculation
     * @param totals
     */
    calculateBases: function (totals) {
        if(this.selectedUser.isManager === true && this.selectedUser.showOpps === false) {
            this.likelyTotal = totals.likely_adjusted;
            this.bestTotal = totals.best_adjusted;
        } else {
            this.likelyTotal = totals.likely_case;
            this.bestTotal = totals.best_case;
        }
    },


    /**
     * take in the totals when they update for the manager/rep worksheet and make sure the rest of the progress model recalculates according to the changes
     * @param totals model that was updated
     */
    recalculateTotals: function (totals) {
        if(this.selectedUser.isManager === true && this.selectedUser.showOpps === false) {
            this.model.set({
                revenue : totals.amount,
                quota_amount : totals.quota
        });
        } else {
            this.model.set({
                closed_amount : totals.won_amount,
                opportunities : totals.included_opp_count,
                revenue : totals.amount
            });
        }
        this.recalculateModel();
    },

    recalculateModel: function () {
        this.model.set({
            closed_likely_amount : this.getAbsDifference(this.likelyTotal, this.model.get('closed_amount')),
            closed_likely_percent : this.getPercent(this.model.get('closed_amount'), this.likelyTotal),
            closed_likely_above : this.checkIsAbove(this.model.get('closed_amount'), this.likelyTotal ),
            closed_best_amount : this.getAbsDifference(this.bestTotal, this.model.get('closed_amount')),
            closed_best_percent : this.getPercent(this.model.get('closed_amount'), this.bestTotal),
            closed_best_above : this.checkIsAbove(this.model.get('closed_amount'), this.bestTotal),
            quota_likely_amount : this.getAbsDifference(this.likelyTotal, this.model.get('quota_amount')),
            quota_likely_percent : this.getPercent(this.likelyTotal, this.model.get('quota_amount')),
            quota_likely_above : this.checkIsAbove(this.likelyTotal, this.model.get('quota_amount')),
            quota_best_amount : this.getAbsDifference(this.bestTotal, this.model.get('quota_amount')),
            quota_best_percent : this.getPercent(this.bestTotal, this.model.get('quota_amount')),
            quota_best_above : this.checkIsAbove(this.bestTotal, this.model.get('quota_amount')),
            pipeline : this.calculatePipelineSize(this.likelyTotal, this.model.get('revenue'), this.model.get('closed_amount'))
        });
        this.render();
    },

    /**
     * determine if one value is bigger than another, used as a shortcut method to determine likely/best is above quota/closed
     * @param caseValue
     * @param stageValue
     * @return {Boolean}
     */
    checkIsAbove: function (caseValue, stageValue) {
        if(caseValue > stageValue)
            return true;

        return false;
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
        if(stageValue > 0) {
            return caseValue / stageValue;
        }
        return 0;
    },

    /**
     * calculates the pipeline size to one significant figure.  based on revenue with closed amount divided by the likely amount
     * @param likelyTotal
     * @param revenue
     * @param closed
     * @return {Number}
     */
    calculatePipelineSize: function (likelyTotal, revenue, closed) {
        var ps = 0;
        if ( likelyTotal > 0 ) {
            ps = (revenue + closed) /  likelyTotal;

            // Round to 1 decimal place
            ps = Math.round( ps * 10 )/10;
        }

        // This value is used in the template.
        return ps;
    },

    /**
     * render override
     * @private
     */
    _render: function () {
        _.extend(this, this.model.toJSON());
        app.view.View.prototype._render.call(this);
    },

    /**
     * set the new time period
     * @param selectedTimePeriod
     */
    updateProgressForSelectedTimePeriod: function (selectedTimePeriod) {
        this.seletedTimePeriod = selectedTimePeriod;
    },

    /**
     * set the new selected user
     * @param selectedUser
     */
    updateProgressForSelectedUser: function (selectedUser) {
        this.selectedUser = selectedUser;
    },

    /**
     * something has changed, so we need to update the progress model depending on this change
     */
    updateProgress: function () {
        var getRollup = false;
        var self = this;
        var urlParams = {};
        var url = this.progressEndpoint;

        //Get the excluded_sales_stage property.  Default to empty array if not set


        if(self.selectedUser != undefined && self.selectedTimePeriod != undefined) {
            if(self.selectedUser.isManager === true && self.selectedUser.showOpps === false)
                getRollup = true;

            url += self.selectedUser.id + "/";
            url += self.selectedTimePeriod.id + "/";
            url += getRollup ? "1/" : "0/";
        }

        app.api.call('read', url, null, null, {
            success: function(data) {
                if(getRollup) {
                    self.model.set({
                        opportunities : data.opportunities,
                        closed_amount : data.closed_amount
                    });
                    self.recalculateModel();
                } else {
                    self.model.set({
                        quota_amount : data.quota_amount
                    });
                    self.recalculateModel();
                }
            }
        });
    }
})
