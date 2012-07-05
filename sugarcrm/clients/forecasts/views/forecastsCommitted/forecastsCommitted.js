/**
 * View that displays header for current app
 * @class View.Views.GridView
 * @alias SUGAR.App.layout.GridView
 * @extends View.View
 */
({
    url : 'rest/v10/Forecasts/committed',

    viewSelector : '.forecastsCommitted',

    _collection : {},

    initialize : function(options) {
        app.view.View.prototype.initialize.call(this, options);
        var self = this;

        //Set defaults
        this.fullName = app.user.get('full_name');
        this.userId = app.user.get('id');
        this.timePeriodId = '';
        this.bestCase = 0;
        this.likelyCase = 0;
        this.historyLog = Array();
        this._collection = this.context.forecasts.committed;
        this._collection.fetch();
        this.totals = null;
        this.previousTotals = null;
        this.showButton = true;
        this.render();
        //Add listeners
        this.context.forecasts.on("change:selectedUser", function(context, user) {
            self.showButton = app.user.get('id') == user.id; 
            self.userId = user.id; 
            self.fullName = user.full_name; self.updateCommitted(); 
        });
        this.context.forecasts.on("change:selectedTimePeriod", function(context, timePeriod) {
            self.timePeriodId = timePeriod.id;
            self.updateCommitted(); 
        });
        this.context.forecasts.on("change:updatedTotals", function(context, totals) {
            self.totals = totals;
        });
    },

    updateCommitted: function() {
        this._collection = this.context.forecasts.committed;
        var urlParams = $.param({
            user_id: encodeURIComponent(this.userId),
            timeperiod_id : encodeURIComponent(this.timePeriodId)
        });
        this._collection.fetch({
            data: urlParams
        });
    },

    bindDataChange: function() {
        if(this._collection)
        {
           this._collection.on("reset", this.refresh, this);
        }
    },

    refresh: function() {
        var self = this;
        $.when(self.buildForecastsCommitted(), self.render());
    },

    buildForecastsCommitted: function() {
        var self = this;
        var count = 0;
        var previousModel;

        //Reset the history log
        self.historyLog = [];
        self.moreLog = [];

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

        //Slice everything after the second element
        if(self.historyLog.length > 2)
        {
           self.moreLog = self.historyLog.splice(2, self.historyLog.length);
        }
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

        if(!self.totals)
        {
           return;
        }

        //If there was a previous entry, check to make sure values have changed
        if(self.previous &&
            (self.totals.best_case == self.previous.best_case &&
             self.totals.likely_case == self.previous.likely_case &&
             self.totals.timeperiod_id == self.previous.timeperiod_id &&
             self.totals.forecast_type == self.previous.forecast_type))
        {
           return;
        }

        self._collection.url = app.config.serverUrl + "/Forecasts/committed";
        var forecast = new Backbone.Model();
        forecast.set('best_case', self.totals.best_case);
        forecast.set('likely_case', self.totals.likely_case);
        forecast.set('timeperiod_id', self.totals.timeperiod_id);
        forecast.set('forecast_type', self.totals.forecast_type);
        forecast.set('amount', self.totals.amount);
        forecast.set('opp_count', self.totals.opp_count);
        self.previous = self.totals;
        self._collection.create(forecast);
        var urlParams = $.param({
            user_id: encodeURIComponent(this.userId),
            timeperiod_id : encodeURIComponent(this.timePeriodId)
        });
        this._collection.fetch({
            data: urlParams
        });
    }
})