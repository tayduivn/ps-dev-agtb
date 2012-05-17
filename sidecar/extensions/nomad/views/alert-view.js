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
            // TODO: Implement

            // This method should return some object that implements 'close' function
            // It can be a custom Backbone.View but not necessarily

            // Alerts are managed by SUGAR.App.alert module (view/alert.js)
            // From any place in the app one can invoke:
            // app.alert.show(key, { ...});
            // app.alert.dismiss(key); -- this calls the 'close' function on whatever object we return from this method

            // For our mobile app the options are:
            // - autoclose: true/false
            // - level: error, warning, success, info
            // - message: already localized message

            // We need to be able to show multiple alerts on top of each other
            // Multiple alerts can be of the same type (level) or different types

            // Some of them are autocloseable with a timeout, others should be manually closeable by tapping 'x' icon

            // See extensions/nomad/mockups/accounts_msg_process_multiple.html
            // and sidecar/src/views/alert-view.js for portal implementation

            // The handlebars template should be in extensions/nomad/templates/alert.hbt

            return null;
        }

    });

})(SUGAR.App);