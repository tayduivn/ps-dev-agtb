(function(app) {
    if (!app.Model) {
        app.Model = {};
    }

    app.Model.Filters = Backbone.Model.extend({
        module: "Forecasts/filters",
        timeperiods: null,
        stages: null,
        probabilities: null,

        initialize: function(attributes, options) {
            var self = this;
            Backbone.Model.prototype.initialize.call(this, attributes, options);
            this.on("change:timeperiods", function() {
                self.timeperiods = new Backbone.Model(self.get("timeperiods"));
            });
            this.on("change:stages", function() {
                self.stages = new Backbone.Model(self.get("stages"));
            });
            this.on("change:probabilities", function() {
                self.probabilities = new Backbone.Model(self.get("probabilities"));
            });
        }
    });

})(SUGAR.App);