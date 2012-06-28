/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.ForecastsSubnavView
 * @alias SUGAR.App.layout.ForecastsSubnavView
 * @extends View.View
 */
({

    initialize : function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.fullName = app.user.get('full_name');
    },

    bindDataChange: function() {
        var self = this;
        this.context.on('change:selectedUser', function(context, user) {
            self.fullName = user.full_name;
            self.render();
        });
        this.context.on('change:selectedTimePeriod', function(context, timePeriod) {
            self.timePeriod = timePeriod.label;
            self.render();
        });
    }

})
