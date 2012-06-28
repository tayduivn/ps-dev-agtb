(function(app) {

    // This view is instantiated by the app module
    // It is referenced in app.config.additionalComponents setting (modify your config.js, see extensions/nomad/sample-config.js)
    app.view.views.AlertView = app.view.View.extend({

        /**
         * Shows an alert.
         * @param options(optional) Alert options.
         * @return {Object} Alert instance.
         */
        show: function(options) {
            var alert = {
                $el: $('<div>'),
                autoClose: !!options.autoClose,
                messages: (_.isString(options.messages)) ? [options.messages] : options.messages,
                type: options.level || 'general',
                close: function() {
                    alert.$el.remove();
                }
            };

            if (alert.autoClose === true) {
                setTimeout(function() {
                    alert.$el.remove();
                }, app.config.alertAutoCloseDelay || 9000);
            }

            var tmpl = app.template.get('alert-panel');
            if (tmpl) alert.$el.append(tmpl(alert)).prependTo(this.$el);

            return alert;
        }
    });

})(SUGAR.App);