/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.FilterView
 * @alias SUGAR.App.layout.FilterView
 * @extends View.View
 */
({

    likelyTotal: 0,
    bestTotal: 0,

    initialize: function (options) {
        _.bindAll(this); // Don't want to worry about keeping track of "this"
        // CSS className must be changed to avoid conflict with Bootstrap CSS.
        options.className = "progressBar";
        app.view.View.prototype.initialize.call(this, options);

        this.model = this.context.forecasts.progress;
        this.updateProgress();
        this.selectedUser = this.context.forecasts.get("selectedUser");
        this.selectedTimePeriod = this.context.forecasts.get("selectedTimePeriod");
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
        if(this.model.has("revenue")) {
            this.calculateBases(totals);
            this.model.set("opportunities", totals.included_opp_count + totals.closed_opp_count);
            this.model.set("revenue", totals.amount);
            this.model.set("closed_amount", totals.closed_amount);
            this.model.set({
                closed_likely_amount : this.getAbsDifference(this.likelyTotal, totals.closed_amount),
                closed_likely_percent : this.getPercent(totals.closed_amount, this.likelyTotal),
                closed_likely_above : this.checkIsAbove(totals.closed_amount, this.likelyTotal ),
                quota_likely_amount : this.getAbsDifference(this.likelyTotal, this.quota_amount),
                quota_likely_percent : this.getPercent(this.likelyTotal, this.quota_amount),
                quota_likely_above : this.checkIsAbove(this.likelyTotal, this.quota_amount),
                closed_best_amount : this.getAbsDifference(this.bestTotal, totals.closed_amount),
                closed_best_percent : this.getPercent(totals.closed_amount, this.bestTotal),
                closed_best_above : this.checkIsAbove(totals.closed_amount, this.bestTotal),
                quota_best_amount : this.getAbsDifference(this.bestTotal, this.quota_amount),
                quota_best_percent : this.getPercent(this.bestTotal, this.quota_amount),
                quota_best_above : this.checkIsAbove(this.bestTotal, this.quota_amount),

                pipeline : this.calculatePipelineSize(this.likelyTotal, this.model.get('revenue'), this.model.get('closed_amount'))
            });
        }
    },

    calculateBases: function (totals) {
        var closed = this.model.get('closed');

        if(this.selectedUser.isManager === true && this.selectedUser.showOpps === false) {
            this.likelyTotal = this.reduceWorksheetManager('likely_case') - closed.amount;
            this.bestTotal = this.reduceWorksheetManager('best_case');
            this.model.set('revenue', this.reduceWorksheetManager('amount') - closed.amount);
            this.revenue = this.model.get('revenue') - closed.amount;
        } else {
            this.likelyTotal = totals.likely_case;
            this.bestTotal = totals.best_case
            this.model.set('revenue', totals.amount);
            this.model.set('closed_amount', totals.closed_amount);
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

        if(self.selectedUser != undefined && self.selectedTimePeriod != undefined) {
            if(self.selectedUser.isManager === true && self.selectedUser.showOpps === false)
                getRollup = true;

            var urlParams = $.param({
                user_id: self.selectedUser.id,
                timePeriodId: self.selectedTimePeriod.id,
                shouldRollup: getRollup ? 1 : 0
            });
        }
        this.model.fetch({
            data: urlParams
        });
    }
})
