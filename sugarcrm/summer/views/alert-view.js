(function(app) {
    /**
     * View that displays errors.
     * @class View.Views.AlertView
     * @extends View.View
     */
    app.view.views.AlertView = app.view.View.extend({

        initialize: function(options) {
            app.view.View.prototype.initialize.call(this, options);
        },
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

            // "process" is the loading indicator .. I didn't name it ;=)
            alertClass = (level === "process" || level === "success" || level === "warning" || level === "info" || level === "error") ? "alert-" + level : "";

            ctx = {
                alertClass:  alertClass,
                title:       title,
                messages:    msg,
                autoClose:   autoClose
            };
            try {
                AlertView = Backbone.View.extend({
                    events : {
                        'click .close' : 'close'
                    },
                    template: "<div class=\"alert {{alertClass}} alert-block {{#if autoClose}}timeten{{/if}}\">" +
                        "<a class=\"close\" data-dismiss=\"alert\">x</a>{{#if title}}<strong>{{{title}}}</strong>{{/if}}" +
                        "{{#each messages}}<p>{{{this}}}</p>{{/each}}</div>",
                    loadingTemplate: "<div class=\"alert {{alertClass}}\">" +
                        "<strong>{{title}}</strong>&hellip;<a class=\"close\" data-dismiss=\"alert\">x</a></div>",
                    initialize: function() {
                        this.render();
                    },
                    close: function() {
                        this.$el.remove();
                    },
                    render: function() {
                        var tpl = (level === 'process') ?
                            Handlebars.compile(this.loadingTemplate) :
                            Handlebars.compile(this.template);

                        this.$el.html(tpl(ctx));
                    }
                });
                thisAlert = new AlertView();
                this.$el.prepend(thisAlert.el).show();

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
