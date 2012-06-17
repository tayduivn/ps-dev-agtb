/**
 * View that displays header for current app
 * @class View.Views.GridView
 * @alias SUGAR.App.layout.GridView
 * @extends View.View
 */
({
    viewSelector : '.forecastsCommitted',

    _collection : {},

    initialize : function(options) {
        app.view.View.prototype.initialize.call(this, options);
        var self = this;

        //Set defaults
        this.fullName = app.user.get('full_name');
        this.userId = app.user.get('id');
        this.bestCase = 0;
        this.likelyCase = 0;
        this.historyLog = Array();
        this._collection = this.context.model.forecasts.committed;
        this._collection.fetch();
        this.render();
        //Add listeners
        this.layout.context.on("change:selectedUser", function(context, user) { self.userId = user.id; self.buildForecastsCommitted(); } );
        this.layout.context.on("change:selectedTimePeriod", function(context, timePeriod) { self.timePeriodId = timePeriod.id; self.buildForecastsCommitted(); });
    },


    bindDataChange: function() {
        var self = this;
        this.context.on('change:selectedUser', function(context, user) {
            self.fullName = user.full_name;
            self._collection = self.context.model.forecasts.committed;
            self._collection.url = app.config.serverUrl + "/Forecasts/committed?timeperiod_id=" + self.timePeriodId + "&user_id=" + self.userId;
            self._collection.fetch();
            self.render();
        });
        this.context.on('change:selectedTimePeriod', function(context, timePeriod) {
            self.timePeriodId = timePeriod.id;
            self._collection = self.context.model.forecasts.committed;
            self._collection.url = app.config.serverUrl + "/Forecasts/committed?timeperiod_id=" + self.timePeriodId + "&user_id=" + self.userId;
            self._collection.fetch();
            self.render();
        });
    },

    buildForecastsCommitted: function() {
        var self = this;
        var count = 0;
        var previous;

        _.each(self._collection.models, function(model)
        {
            //Get the first entry
            if(count == 0)
            {
              self.bestCase = model.get('best_case');
              self.likelyCase = model.get('likely_case');
              previous = model;
            } else {
              var hb = Handlebars.compile(SUGAR.language.get('Forecasts', 'LBL_PREVIOUS_COMMIT'));
              self.previousText = hb({'likely_case' : model.get('likely_case')});
              self.previousLikelyCase = model.get('likely_case');
              self.previousBestCase = model.get('best_case');
              var id = model.get('id');
              //hb = Handlebars.compile(SUGAR.language.get('Forecasts', 'LBL_PREVIOUS_COMMIT'));
              self.historyLog.push(self.createHistoryLog(model, previous));
              previous = model;
            }
            count++;
        });

        //Call render again
        this.render();
    },

    createHistoryLog: function(current, previous) {
        var best_difference = current.get('best_case') - previous.get('best_case');
        var best_changed = best_difference != 0;
        var best_direction = best_difference > 0 ? 'LBL_UP' : (best_difference < 0 ? 'LBL_DOWN' : '');
        var likely_difference = current.get('likely_case') - previous.get('likely_case');
        var likely_changed = likely_difference != 0;
        var likely_direction = likely_difference > 0 ? 'LBL_UP' : (likely_difference < 0 ? 'LBL_DOWN' : '');
        var args = Array();
        var text = 'LBL_COMMITTED_HISTORY_NONE_CHANGED';
        if(best_changed && likely_changed)
        {
            args[0] = SUGAR.language.get(best_direction, 'Forecasts');
            args[1] = Math.abs(best_difference);
            args[2] = current.get('best_case');
            args[3] = SUGAR.language.get(likely_direction, 'Forecasts');
            args[4] = Math.abs(likely_difference);
            args[5] = current.get('likely_case');
            text = 'LBL_COMMITTED_HISTORY_BOTH_CHANGED';
        } else if (!best_changed && likely_changed) {
            args[0] = SUGAR.language.get(likely_direction, 'Forecasts');
            args[1] = Math.abs(likely_difference);
            args[2] = current.get('likely_case');
            text = 'LBL_COMMITTED_HISTORY_LIKELY_CHANGED';
        } else if (best_changed && !likely_changed) {
            args[0] = SUGAR.language.get(best_direction, 'Forecasts');
            args[1] = Math.abs(best_difference);
            args[2] = current.get('best_case');
            text = 'LBL_COMMITTED_HISTORY_BEST_CHANGED';
        }

        //Compile the language string for the log
        var hb = Handlebars.compile("{{str_format key module args}}");
        var text = hb({'key' : text, 'module' : 'Forecasts', 'args' : args});

        //Compile the language string for the date modified portion
        //TODO
        return text;

    },

    events: {
        "click a[id=commit_forecast]" : "commitForecast"
    },

    commitForecast: function() {
        model = this.context.model.committed;
    }
})