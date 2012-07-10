(function(app) {

    // This view is instantiated by the app module
    // It is referenced in app.config.additionalComponents setting (modify your config.js, see extensions/nomad/sample-config.js)
    app.view.views.AlertView = app.view.View.extend({

        /**
         * Eo not initially render the alert template
         */
        _render: function() {},

        /**
         * Remove alert from page.
         */
        close: function() {
            this.$el.empty();
        },

        /**
         * Shows an alert.
         *
         * @param options Alert options.
         */
        show: function(options) {
            var tmpl = app.template.get('alert');
            var data = {
                type: options.level || 'general',
                messages: (_.isString(options.messages)) ? [options.messages] : options.messages
            };

            this.$el.append(tmpl(data));
            return this;
        }
    });

})(SUGAR.App);