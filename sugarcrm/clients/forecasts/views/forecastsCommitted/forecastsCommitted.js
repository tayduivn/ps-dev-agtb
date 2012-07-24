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
     * The url for the REST endpoint
     */
    url : 'rest/v10/Forecasts/committed',

    /**
     * The class selector representing the element which contains the view output
     */
    viewSelector : '.forecastsCommitted',

    /**
     * Stores the Backbone collection of Forecast models
     */
    _collection : {},

    /*
     * Stores the name to display in the view
     */
    fullName : '',

    /**
     * Stores the best case to display in the view
     */
    bestCase : 0,

    /**
     * Stores the likely case to display in the view
     */
    likelyCase : 0,

    /**
     * Used to query for the user_id value in Forecasts
     */
    userId : '',

    /**
     * Used to query for the timeperiod_id value in Forecasts
     */
    timePeriodId : '',

    /**
     * Used to query for the forecast_type value in Forecasts
     */
    forecastType : 'Direct',

    /**
     * Stores the historical log of the Forecast entries
     */
    historyLog : Array(),

    /**
     * Stores the Forecast totals to use when creating a new entry
     */
    totals : null,

    /**
     * Stores the previous totals to display in the view
     */
    previousTotals : null,

    /**
     * Used to determine whether or not to show the Commit button
     */
    showButton : true,

    initialize : function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this._collection = this.context.forecasts.committed;
        this.fullName = app.user.get('full_name');
        this.userId = app.user.get('id');
        this.forecastType = app.user.get('isManager') ? 'Rollup' : 'Direct';
        this.timePeriodId = app.defaultSelections.timeperiod_id.id;
    },

    updateCommitted: function() {
        var urlParams = $.param({
            user_id: this.userId,
            timeperiod_id : this.timePeriodId,
            forecast_type : this.forecastType
        });

        this._collection.fetch({
            data: urlParams
        });
    },

    /**
     * returns boolean value indicating whether or not to show the commit button
     */
    showCommitButton: function(id) {
        return app.user.get('id') == id;
    },

    bindDataChange: function() {

        var self = this;

        this._collection = this.context.forecasts.committed;
        this._collection.on("reset", function() { self.buildForecastsCommitted() }, this);

        if(this.context && this.context.forecasts) {
            this.context.forecasts.on("change:selectedUser", function(context, user) {
                self.showButton = self.showCommitButton(user.id);
                self.userId = user.id;
                self.fullName = user.full_name;
                self.forecastType = user.showOpps ? 'Direct' : 'Rollup';
                self.updateCommitted();
            }, this);
            this.context.forecasts.on("change:selectedTimePeriod", function(context, timePeriod) {
                self.timePeriodId = timePeriod.id;
                self.updateCommitted();
            }, this);
            this.context.forecasts.on("change:updatedTotals", function(context, totals) {
                self.totals = totals;
            }, this);
        }
    },

    buildForecastsCommitted: function() {
        var self = this;
        var count = 0;
        var previousModel;

        //Reset the history log
        self.historyLog = [];
        self.moreLog = [];
        self.previousText = "Previous Commit: 0";
        self.previousLikelyCase = 0;
        self.previousBestCase = 0;
        self.bestCase = 0;
        self.likelyCase = 0;

        _.each(self._collection.models, function(model)
        {
            //Get the first entry
            if(count == 0)
            {
              self.bestCase = model.get('best_case');
              self.likelyCase = model.get('likely_case');
              previousModel = model;
            } else {
              if(count == 1)
              {
                  var hb = Handlebars.compile(SUGAR.language.get('Forecasts', 'LBL_PREVIOUS_COMMIT'));
                  self.previousText = hb({'likely_case' : model.get('likely_case')});
                  self.previousLikelyCase = model.get('likely_case');
                  self.previousBestCase = model.get('best_case');
              }
              self.historyLog.push(self.createHistoryLog(model, previousModel));
              previousModel = model;
            }
            count++;
        });

        //Slice everything after the second element and store in historyLog variable
        if(self.historyLog.length > 2)
        {
           self.moreLog = self.historyLog.splice(2, self.historyLog.length);
        }

        self.render();
    },

    createHistoryLog: function(current, previousModel) {
        var best_difference = previousModel.get('best_case') - current.get('best_case');
        var best_changed = best_difference != 0;
        var best_direction = best_difference > 0 ? 'LBL_UP' : (best_difference < 0 ? 'LBL_DOWN' : '');
        var likely_difference = previousModel.get('likely_case') - current.get('likely_case');
        var likely_changed = likely_difference != 0;
        var likely_direction = likely_difference > 0 ? 'LBL_UP' : (likely_difference < 0 ? 'LBL_DOWN' : '');
        var args = Array();
        var text = 'LBL_COMMITTED_HISTORY_NONE_CHANGED';

        if(best_changed && likely_changed)
        {
            args[0] = App.lang.get(best_direction, 'Forecasts');
            args[1] = Math.abs(best_difference);
            args[2] = previousModel.get('best_case');
            args[3] = App.lang.get(likely_direction, 'Forecasts');
            args[4] = Math.abs(likely_difference);
            args[5] = previousModel.get('likely_case');
            text = 'LBL_COMMITTED_HISTORY_BOTH_CHANGED';
        } else if (!best_changed && likely_changed) {
            args[0] = App.lang.get(likely_direction, 'Forecasts');
            args[1] = Math.abs(likely_difference);
            args[2] = current.get('likely_case');
            text = 'LBL_COMMITTED_HISTORY_LIKELY_CHANGED';
        } else if (best_changed && !likely_changed) {
            args[0] = App.lang.get(best_direction, 'Forecasts');
            args[1] = Math.abs(best_difference);
            args[2] = current.get('best_case');
            text = 'LBL_COMMITTED_HISTORY_BEST_CHANGED';
        }

        //Compile the language string for the log
        var hb = Handlebars.compile("{{str_format key module args}}");
        var text = hb({'key' : text, 'module' : 'Forecasts', 'args' : args});

        var current_date = new Date(current.get('date_entered'));
        var previous_date = new Date(previousModel.get('date_entered'));

        var yearDiff = current_date.getYear() - previous_date.getYear();
        var monthsDiff = current_date.getMonth() - previous_date.getMonth();

        var text2 = '';

        if(yearDiff == 0 && monthsDiff < 2)
        {
          hb = Handlebars.compile("{{str_format key module args}}");
          args = [previous_date.toString()];
          text2 = hb({'key' : 'LBL_COMMITTED_THIS_MONTH', 'module' : 'Forecasts', 'args' : args});
        } else {
          hb = Handlebars.compile("{{str_format key module args}}");
          args = [monthsDiff, previous_date.toString()];
          text2 = hb({'key' : 'LBL_COMMITTED_MONTHS_AGO', 'module' : 'Forecasts', 'args' : args});
        }

        return {'text' : text, 'text2' : text2};

    },

    /**
     * Add a click event listener to the commit button
     */
    events: {
        "click a[id=commit_forecast]" : "commitForecast"
    },

    /**
     * commit the forecast and by creating a forecast entry if the totals have been updated and the new forecast entry
     * is different from the previous one (best_case and likely_case are not exactly identical)
     *
     */
    commitForecast: function() {
        var self = this;

        //If the totals have not been set, don't save
        if(!self.totals)
        {
           return;
        }

        //If there was a previous entry, check to make sure values have changed
        if(self.previous &&
            (self.totals.best_case == self.previous.best_case &&
             self.totals.likely_case == self.previous.likely_case &&
             self.totals.timeperiod_id == self.previous.timeperiod_id))
        {
           return;
        }

        var forecast = new Backbone.Model();
        forecast.url = self.url;
        forecast.set('best_case', self.totals.best_case);
        forecast.set('likely_case', self.totals.likely_case);
        forecast.set('timeperiod_id', self.totals.timeperiod_id);
        forecast.set('forecast_type', self.forecastType);
        forecast.set('amount', self.totals.amount);
        forecast.set('opp_count', self.totals.opp_count);
        forecast.save();
        self.previous = self.totals;
        self._collection.url = self.url;
        self._collection.add(forecast);
        self.updateCommitted();
    }
})
