(function(app) {
    if (!app.Model) {
        app.Model = {};
    }

    /**
     * Model for the progress view on the forecasts layout.
     * @type {Backbone.Model}
     */
    app.Model.Progress = Backbone.Model.extend({
        module: 'Forecasts/progress',

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