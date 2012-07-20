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
        this.likelyTotal = 0;
        this.bestTotal = 0;
        this.worksheetCollection = this.context.forecasts.worksheet;
        this.worksheetManagerCollection = this.context.forecasts.worksheetmanager;
    },

    bindDataChange: function () {

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
            this.context.forecasts.on("change:selectedUser change:selectedTimePeriod", this.updateProgressForSelectedUser);
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

    calculateLikelyToQuota: function () {
        var quota = this.model.get('quota');
        if(quota == undefined) {
            quota = {amount: 0, best_case : {amount: 0, above: false, percent: 0.0}, likely_case : {amount: 0, above: false, percent: 0.0}};
        }
        quota.amount = parseInt(quota.amount, 10);

        quota.likely_case.amount = this.getAbsDifference(this.likelyTotal, quota.amount);
        quota.likely_case.above = this.checkIsAbove(this.likelyTotal, quota.amount);
        quota.likely_case.percent = this.getPercent(this.likelyTotal, quota.amount);

        this.model.set('quota', quota);
    },

    calculateBestToQuota: function () {
        var quota = this.model.get('quota');
        if(quota == undefined) {
            quota = {amount: 0, best_case : {amount: 0, above: false, percent: 0.0}, likely_case : {amount: 0, above: false, percent: 0.0}};
        }
        quota.amount = parseInt(quota.amount, 10);

        quota.best_case.amount = this.getAbsDifference(this.bestTotal, quota.amount);
        quota.best_case.above = this.checkIsAbove(this.bestTotal, quota.amount);
        quota.best_case.percent = this.getPercent(this.bestTotal, quota.amount);

        this.model.set('quota', quota);
    },

    calculateLikelyToClose: function () {
        var closed = this.model.get('closed');

        if(closed == undefined) {
            closed = {amount: 0, best_case : {amount: 0, above: false, percent: 0.0}, likely_case : {amount: 0, above: false, percent: 0.0}};
        }
        closed.amount = parseInt(closed.amount, 10);

        closed.likely_case.amount = this.getAbsDifference(this.likelyTotal, closed.amount);
        closed.likely_case.above = this.checkIsAbove(this.likelyTotal, closed.amount);
        closed.likely_case.percent = this.getPercent(this.likelyTotal, closed.amount);

        this.model.set('closed', closed);
    },

    calculateBestToClose: function () {
        var closed = this.model.get('closed');
        if(closed == undefined) {
            closed = {amount: 0, best_case : {amount: 0, above: false, percent: 0.0}, likely_case : {amount: 0, above: false, percent: 0.0}};
        }
        closed.amount = parseInt(closed.amount, 10);

        closed.best_case.amount = this.getAbsDifference(this.bestTotal, closed.amount);
        closed.best_case.above = this.checkIsAbove(this.bestTotal, closed.amount);
        closed.best_case.percent = this.getPercent(this.bestTotal, closed.amount);

        this.model.set('closed', closed);
    },

    reduceWorksheet: function(attr) {
      return this.worksheetCollection.reduce(function(memo, model) {
                          // Only add up values that are "included" in the worksheet.
                          if ( model.get('forecast') === true && !(/closed (?:won|lost)/i).test(model.get("sales_stage")) ) {
                              memo += parseInt(model.get(attr), 10);
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
        var currentUser = this.context.forecasts.get("selectedUser");

        if(currentUser.isManager === true && currentUser.showOpps === false) {
            this.likelyTotal = this.reduceWorksheetManager('likely_case');
            this.bestTotal = this.reduceWorksheetManager('best_case');
            this.model.set('revenue', this.reduceWorksheetManager('amount'));
            this.revenue = this.model.get('revenue');
        } else {
            this.likelyTotal = this.reduceWorksheet('likely_case');
            this.bestTotal = this.reduceWorksheet('best_case');
            this.model.set('revenue', this.reduceWorksheet('amount'));
            this.revenue = this.model.get('revenue');
        }
    },
    
    calculatePipelineSize: function () {
        var ps = 0;

        if ( this.likelyTotal > 0 ) {
            ps = this.model.get('revenue') / this.likelyTotal;

            // Round to 1 decimal place
            ps = Math.round( ps * 10 )/10;
        }

        // This value is used in the template.
        this.model.set('pipeline',ps);
    },

    recalculate: function () {
        this.calculateBases();
        this.calculatePipelineSize();
        this.calculateBestToClose();
        this.calculateBestToQuota();
        this.calculateLikelyToClose();
        this.calculateLikelyToQuota();
        this.render();
    },

    _render: function () {
        _.extend(this, this.model.toJSON());
        app.view.View.prototype._render.call(this);
    },

    updateProgressForSelectedUser: function (context, user) {
        var self = this;
        var getRollup = false;
        var selectedUser = self.context.forecasts.get("selectedUser");

        if(selectedUser.isManager === true && selectedUser.showOpps === false)
            getRollup = true;

        var urlParams = $.param({
            user_id: selectedUser.id,
            timePeriodId: self.context.forecasts.get("selectedTimePeriod").id,
            shouldRollup: getRollup ? 1 : 0
        });
        this.model.fetch({
            data: urlParams
        });
    }
})
