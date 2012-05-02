(function(app) {
    /**
     * View that displays errors.
     * @class View.Views.AlertView
     * @extends View.View
     */
    app.view.views.AlertView = app.view.View.extend({
        initialize: function(options) {
            app.events.on("app:alert", function(args) {
                this.render(args);
            }, this);
            app.view.View.prototype.initialize.call(this, options);
        },
        render: function(args) {
            var args = args || [];
            var level = args[0];
            var alertClass = (level === "info" || level === "error") ? "alert-" + level : "";
            var ctx = {
                alertClass: alertClass,
                message: args[1]
            };
            if (this.template) {
                try {
                    this.$el.prepend(this.template(ctx));
                } catch (e) {
                    app.logger.error("Failed to render '" + this.name + "' view.\n" + e.message);
                    // TODO: trigger app event to render an error message
                }
            }
        }
    });
})(SUGAR.App);