/**
 * View that displays header for current app
 * @class View.Views.GridView
 * @alias SUGAR.App.layout.GridView
 * @extends View.View
 */
({
    viewSelector: '.forecastsCommitted',

    initialize : function(options) {
        app.view.View.prototype.initialize.call(this, options);
        //Set defaults
        this.fullName = app.user.get('full_name');
        this.bestCase = 0;
        this.likelyCase = 0;
    },

    bindDataChange: function() {
        var self = this, model = this.context.model.forecasts.committed;
        model.on('change', function() {
            self.buildForecastsCommitted(model);
        });
    },

    buildForecastsCommitted: function(model) {
        var self = this;
        var latest = model.get('latest');
        if(latest)
        {
            self.likelyCase = latest['likely_case'];
            self.bestCase = latest['best_case'];
        }

        var previous = model.get('previous');
        if(previous)
        {
            self.previousLikelyCase = previous['likely_case'];
            self.previousBestCase = previous['best_case'];
            self.previousText = previous['text'];
            self.history = new Array();

            //Store history model in array so handlebar template may process this
            var historyLog = model.get('history');
            _.each(historyLog, function(data, key)
            {
                if (historyLog.hasOwnProperty(key)){
                    self.history.push({
                      'text' : data['text'],
                      'modified' : data['modified']
                    });
                }
            });

        }

        //Call render again
        this.render();
    },

    events: {
        "click a[id=commit_forecast]" : "commitForecast"
    },

    commitForecast: function() {
        model = this.context.model.committed;
    }
})