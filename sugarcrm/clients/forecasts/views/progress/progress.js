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

    bindDataChange: function () {

        var self = this;

        if (this.model) {
            this.model.on('change reset', this.render);
        }
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
                self.recalculate(totals);
            });
            this.context.forecasts.on("change:updatedManagerTotals", function(context, totals) {
                self.recalculate(totals);

            });
        }
    },

    recalculate: function (totals) {
        this.calculateBases(totals);

        if(this.selectedUser.isManager === true && this.selectedUser.showOpps === false) {
            var closedAmount = this.model.get('closed_amount');
            this.model.set({
                closed_likely_amount : this.getAbsDifference(this.likelyTotal, closedAmount),
                closed_likely_percent : this.getPercent(closedAmount, this.likelyTotal),
                closed_likely_above : this.checkIsAbove(closedAmount, this.likelyTotal ),
                closed_best_amount : this.getAbsDifference(this.bestTotal, closedAmount),
                closed_best_percent : this.getPercent(closedAmount, this.bestTotal),
                closed_best_above : this.checkIsAbove(closedAmount, this.bestTotal),
                revenue : totals.amount,
                quota_amount : totals.quota,
                quota_likely_amount : this.getAbsDifference(this.likelyTotal, totals.quota),
                quota_likely_percent : this.getPercent(this.likelyTotal, totals.quota),
                quota_likely_above : this.checkIsAbove(this.likelyTotal, totals.quota),
                quota_best_amount : this.getAbsDifference(this.bestTotal, totals.quota),
                quota_best_percent : this.getPercent(this.bestTotal, totals.quota),
                quota_best_above : this.checkIsAbove(this.bestTotal, totals.quota),
                pipeline : this.calculatePipelineSize(this.likelyTotal, totals.amount, closedAmount)
            });
        } else {
            var quotaAmount = this.model.get('quota_amount');
            this.model.set({
                closed_amount : totals.closed_amount,
                opportunities : totals.included_opp_count,
                closed_likely_amount : this.getAbsDifference(this.likelyTotal, totals.closed_amount),
                closed_likely_percent : this.getPercent(totals.closed_amount, this.likelyTotal),
                closed_likely_above : this.checkIsAbove(totals.closed_amount, this.likelyTotal ),
                closed_best_amount : this.getAbsDifference(this.bestTotal, totals.closed_amount),
                closed_best_percent : this.getPercent(totals.closed_amount, this.bestTotal),
                closed_best_above : this.checkIsAbove(totals.closed_amount, this.bestTotal),
                revenue : totals.amount,
                quota_likely_amount : this.getAbsDifference(this.likelyTotal, quotaAmount),
                quota_likely_percent : this.getPercent(this.likelyTotal, quotaAmount),
                quota_likely_above : this.checkIsAbove(this.likelyTotal, quotaAmount),
                quota_best_amount : this.getAbsDifference(this.bestTotal, quotaAmount),
                quota_best_percent : this.getPercent(this.bestTotal, quotaAmount),
                quota_best_above : this.checkIsAbove(this.bestTotal, quotaAmount),
                pipeline : this.calculatePipelineSize(this.likelyTotal, totals.amount, totals.closed_amount)
            });
        }
    },

    calculateBases: function (totals) {
        var closed = this.model.get('closed');

        if(this.selectedUser.isManager === true && this.selectedUser.showOpps === false) {
            this.likelyTotal = totals.likely_adjusted;
            this.bestTotal = totals.best_adjusted;
        } else {
            this.likelyTotal = totals.likely_case;
            this.bestTotal = totals.best_case;
        }
    },

    checkIsAbove: function (caseValue, stageValue) {
        if(caseValue > stageValue)
            return true;

        return false;
    },

    getAbsDifference: function (caseValue, stageValue) {
        return Math.abs(stageValue - caseValue);
    },

    getPercent: function (caseValue, stageValue) {
        if(stageValue > 0) {
            return caseValue / stageValue;
        }
        return 0;
    },

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

    reduceWorksheetManager: function(attr) {
      return this.worksheetManagerCollection.reduce(function(memo, model) {
                          // Only add up values that are "included" in the worksheet.
                        memo += parseInt(model.get(attr), 10);
                        return memo;
                      }, 0);
    },

    _render: function () {
        _.extend(this, this.model.toJSON());
        app.view.View.prototype._render.call(this);
    },

    updateProgressForSelectedTimePeriod: function (selectedTimePeriod) {
        this.seletedTimePeriod = selectedTimePeriod;
    },

    updateProgressForSelectedUser: function (selectedUser) {
        this.selectedUser = selectedUser;
    },

    updateProgress: function () {
        var getRollup = false;
        var self = this;
        var urlParams = {};
        var url = this.progressEndpoint;

        //Get the excluded_sales_stage property.  Default to empty array if not set
        app.config.excluded_sales_stages = app.config.excluded_sale_stages || [];
        app.config.committed_probability = app.config.committed_probability || 101;


        if(self.selectedUser != undefined && self.selectedTimePeriod != undefined) {
            if(self.selectedUser.isManager === true && self.selectedUser.showOpps === false)
                getRollup = true;

            url += self.selectedUser.id + "/";
            url += self.selectedTimePeriod.id + "/";
            url += getRollup ? "1/" : "0/";
            url += app.config.excluded_sales_stages + "/";
            url += app.config.committed_probability + "/";

            urlParams = {
                user_id: self.selectedUser.id,
                timeperiod_id: self.selectedTimePeriod.id,
                should_rollup: getRollup ? 1 : 0,
                excluded_sales_stages : app.config.excluded_sales_stages,
                committed_probability : app.config.committed_probability
            };
        }


        app.api.call('read', url, null, {
            success: function(data) {
                if(getRollup) {
                    self.model.set({
                        opportunities : data.opportunities,
                        closed_amount : data.closed_amount
                    });
                } else {
                    self.model.set({
                        quota_amount : data.quota_amount,
                        quota_likely_amount : self.getAbsDifference(self.likelyTotal, data.quota_amount),
                        quota_likely_percent : self.getPercent(self.likelyTotal, data.quota_amount),
                        quota_likely_above : self.checkIsAbove(self.likelyTotal, data.quota_amount),
                        quota_best_amount : self.getAbsDifference(self.bestTotal, data.quota_amount),
                        quota_best_percent : self.getPercent(self.bestTotal, data.quota_amount),
                        quota_best_above : self.checkIsAbove(self.bestTotal, data.quota_amount)
                    });
                }
            }

        });
    }
})
