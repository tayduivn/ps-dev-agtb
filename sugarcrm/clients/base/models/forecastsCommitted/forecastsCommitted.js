(function(app) {
    if (!app.Model) {
        app.Model = {};
    }

    app.Model.Committed = Backbone.Model.extend({
        url: app.config.serverUrl + '/Forecasts/forecastsCommitted',

        initialize: function(attributes, options) {
            Backbone.Model.prototype.initialize.call(this, attributes, options);
            this.setModelBindings()
        },

        setModelBindings: function() {
            var self = this;
            this.on('change', function() {
                _.each(this.attributes, function(data, key) {
                    if (self.isNew()) {
                        self[key] = new Backbone.Model(self.get(key));
                    } else if (self.hasChanged(key)) {
                        self[key].set(self.get(key));
                    }
                });
            });
        }

    });

})(SUGAR.App);