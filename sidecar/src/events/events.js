(function(app) {
    app.augment("events", _.extend({
        publish: function(event, context) {
            context.on(event, function(args) {
                this.trigger(event, args);
            }, this);
        },

        clear: function(event, context) {
            if (arguments.length < 2) {
                event.off();
            } else {
                context.off(event);
            }
        }
    }, Backbone.Events));
})(SUGAR.App);