/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.FilterView
 * @alias SUGAR.App.layout.FilterView
 * @extends View.View
 */
({

    likelyTotal: 0,
    bestTotal: 0,
    defaultModel: {amount: 0, best_case : {amount: 0, above: false, percent: 0.0}, likely_case : {amount: 0, above: false, percent: 0.0}},

    initialize: function (options) {
        _.bindAll(this); // Don't want to worry about keeping track of "this"
        // CSS className must be changed to avoid conflict with Bootstrap CSS.
        options.className = "progressBar";
        app.view.View.prototype.initialize.call(this, options);
        this.model = this.context.forecasts.progress;
        this.selectedUser = this.context.forecasts.get("selectedUser");
        this.selectedTimePeriod = this.context.forecasts.get("selectedTimePeriod");
        this.likelyTotal = 0;
        this.bestTotal = 0;
        this.worksheetCollection = this.context.forecasts.worksheet;
        this.worksheetManagerCollection = this.context.forecasts.worksheetmanager;
    },

    bindDataChange: function () {

        var self = this;

        if (this.model) {
            this.model.on('change', this.render);
        }
        if (this.worksheetCollection) {
            this.worksheetCollection.on('change reset', this.recalculate);
        }
        if (this.worksheetManagerCollection) {
            this.worksheetManagerCollection.on('change reset', this.recalculate);
        }
        if (this.context.forecasts) {
            this.context.forecasts.on("change:selectedUser",
            function(context, selectedUser) {
                this.updateProgressForSelectedUser(selectedUser);
            }, this);

            this.context.forecasts.on("change:selectedTimePeriod",
            function(context, selectedTimePeriod) {
                this.updateProgressForSelectedTimePeriod(selectedTimePeriod);
            }, this);
            this.context.forecasts.on("change:updatedTotals", function(context, totals) {
                self.recalculate();
            });
            this.context.forecasts.on("change:updatedManagerTotals", function(context, totals) {
                self.recalculate();
            });
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

    calculateLikelyToQuota: function (quota) {
        if(quota == undefined) {
            quota = this.defaultModel;
        }
        quota.amount = parseInt(quota.amount, 10);

        quota.likely_case.amount = this.getAbsDifference(this.likelyTotal, quota.amount);
        quota.likely_case.above = this.checkIsAbove(this.likelyTotal, quota.amount);
        quota.likely_case.percent = this.getPercent(this.likelyTotal, quota.amount);

        return quota;
    },

    calculateBestToQuota: function (quota) {
        if(quota == undefined) {
            quota = this.defaultModel;
        }
        quota.amount = parseInt(quota.amount, 10);

        quota.best_case.amount = this.getAbsDifference(this.bestTotal, quota.amount);
        quota.best_case.above = this.checkIsAbove(this.bestTotal, quota.amount);
        quota.best_case.percent = this.getPercent(this.bestTotal, quota.amount);

        return quota;
    },

    calculateLikelyToClose: function (closed) {

        if(closed == undefined) {
            closed = this.defaultModel;
        }
        closed.amount = parseInt(closed.amount, 10);

        closed.likely_case.amount = this.getAbsDifference(this.likelyTotal, closed.amount);
        closed.likely_case.above = this.checkIsAbove(closed.amount, this.likelyTotal );
        closed.likely_case.percent = this.getPercent(closed.amount, this.likelyTotal);

        return closed;
    },

    calculateBestToClose: function (closed) {
        if(closed == undefined) {
            closed = this.defaultModel;
        }
        closed.amount = parseInt(closed.amount, 10);

        closed.best_case.amount = this.getAbsDifference(this.bestTotal, closed.amount);
        closed.best_case.above = this.checkIsAbove(closed.amount, this.bestTotal);
        closed.best_case.percent = this.getPercent(closed.amount, this.bestTotal);

        return closed
    },

    reduceWorksheet: function(attr) {
      return this.worksheetCollection.reduce(function(memo, model) {
                          // Only add up values that are "included" in the worksheet.
                          if ( (model.get('forecast') === true || model.get('forecast') === '1') && !(/closed (?:won|lost)/i).test(model.get("sales_stage")) ) {
                              memo += parseInt(model.get(attr), 10);
                          }
                          return memo;
                      }, 0);
    },

    countWorksheetOpportunities: function() {
      return this.worksheetCollection.reduce(function(memo, model) {
                          // Only add up values that are "included" in the worksheet.
                          if ( (model.get('forecast') === true || model.get('forecast') === '1')&& !(/closed (?:won|lost)/i).test(model.get("sales_stage")) ) {
                              memo ++;
                          }
                          return memo;
                      }, 0);
    },

    reduceWorksheetManager: function(attr) {
      return this.worksheetManagerCollection.reduce(function(memo, model) {
                          // Only add up values that are "included" in the worksheet.
                        memo += parseInt(model.get(attr), 10);
                        return memo;
                      }, 0);
    },

    calculateBases: function () {
        var quota = this.model.get('quota');

        if(this.selectedUser.isManager === true && this.selectedUser.showOpps === false) {
            this.likelyTotal = this.reduceWorksheetManager('likely_case');
            this.bestTotal = this.reduceWorksheetManager('best_case');
            this.model.set('revenue', this.reduceWorksheetManager('amount'));
            this.revenue = this.model.get('revenue');
        } else {
            this.likelyTotal = this.reduceWorksheet('likely_case');
            this.bestTotal = this.reduceWorksheet('best_case');
            this.model.set('quota', this.reduceWorksheet('amount'));
        }

        this.model.set('quota', quota);
        this.quota = quota;
    },

    calculatePipelineSize: function (likelyTotal, revenue) {
        var ps = 0;

        if ( likelyTotal > 0 ) {
            ps = revenue / likelyTotal;

            // Round to 1 decimal place
            ps = Math.round( ps * 10 )/10;
        }

        // This value is used in the template.
        return ps;
    },

    recalculate: function () {
        this.calculateBases();
        this.model.set('pipeline', this.calculatePipelineSize(this.likelyTotal, this.model.get('revenue')));
        this.model.set('closed', this.calculateBestToClose(this.model.get('closed')));
        this.model.set('quota', this.calculateBestToQuota(this.model.get('quota')));
        this.model.set('closed', this.calculateLikelyToClose(this.model.get('closed')));
        this.model.set('quota', this.calculateLikelyToQuota(this.model.get('quota')));
        this.render();
    },

    _render: function () {
        _.extend(this, this.model.toJSON());
        app.view.View.prototype._render.call(this);
    },

    updateProgressForSelectedTimePeriod: function (selectedTimePeriod) {
        this.seletedTimePeriod = selectedTimePeriod;
        this.updateProgress();

    },

    updateProgressForSelectedUser: function (selectedUser) {
        this.selectedUser = selectedUser;
        this.updateProgress();
    },

    updateProgress: function () {
        var getRollup = false;
        var self = this;

        if(self.selectedUser.isManager === true && self.selectedUser.showOpps === false)
            getRollup = true;

        var urlParams = $.param({
            user_id: self.selectedUser.id,
            timePeriodId: self.selectedTimePeriod.id,
            shouldRollup: getRollup ? 1 : 0
        });
        this.model.fetch({
            data: urlParams
        });
    }
})
