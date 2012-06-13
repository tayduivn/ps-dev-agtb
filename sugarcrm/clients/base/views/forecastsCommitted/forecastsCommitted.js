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

        var history = model.get('history');
        if(history)
        {
            _.each(history, function(data, key)
            {
                console.log(data);
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