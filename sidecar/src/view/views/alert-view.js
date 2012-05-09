(function(app) {
    /**
     * View that displays errors.
     * @class View.Views.AlertView
     * @extends View.View
     */
    app.view.views.AlertView = app.view.View.extend({

        /**
         * Displays an alert message and returns alert instance. 
         * @param {Object} options
         * @return {Backbone.View} Alert instance 
         * @method
         */
        show: function(options) {
            var level, title, msg, thisAlert, autoClose, alertClass, ctx, AlertView;
            if (!options) {
                return false;
            }

            level     = options.level ? options.level : 'info';
            title     = options.title ? options.title : null;
            msg       = (_.isString(options.messages)) ? [options.messages] : options.messages;
            autoClose = options.autoClose ? options.autoClose : false;

            alertClass = (level === "warning" || level === "info" || level === "error") ? "alert-" + level : "";

            ctx = {
                alertClass:  alertClass,
                title:       title,
                messages:    msg,
                autoClose:   autoClose
            };
            try {
                AlertView = Backbone.View.extend({
                    template: "<div class=\"alert {{alertClass}} alert-block {{#if autoClose}}timeten{{/if}}\">" +
                        "<a class=\"close\" data-dismiss=\"alert\" href=\"#\">x</a>{{#if title}}<strong>{{title}}</strong>{{/if}}" +
                        "{{#each messages}}<p>{{this}}</p>{{/each}}</div>",
                    initialize: function() {
                        this.render();
                    },
                    close: function() {
                        this.$el.remove();
                    },
                    render: function() {
                        var tpl = Handlebars.compile(this.template);
                        this.$el.html(tpl(ctx));
                    }
                });
                thisAlert = new AlertView();
                this.$el.prepend(thisAlert.el);

                if(autoClose) {
                    setTimeout(function(){$('.timeten').fadeOut().remove();},9000);
                }
                return thisAlert;

            } catch (e) {
                app.logger.error("Failed to render '" + this.name + "' view.\n" + e.message);
                return null;
                // TODO: trigger app event to render an error message
            }
        }
    });
})(SUGAR.App);
