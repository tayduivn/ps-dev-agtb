/**
 * View that displays header for current app
 * @class View.Views.GridView
 * @alias SUGAR.App.layout.GridView
 * @extends View.View
 */
({
    viewSelector: '.forecastsCommitted',

    bindDataChange: function() {
        var self = this,
            model = this.context.model.committed;

        model.on('change', function() {
            self.buildForecastsCommitted(this);
        });
    },

    buildForecastsCommitted: function(model) {
        var self = this;
        _.each(model.attributes, function(data, key) {

        });
    },

    events: {
        "click a[id=commit_forecast]" : "commitForecast"
    },

    commitForecast: function() {
        model = this.context.model.committed;
    }
})