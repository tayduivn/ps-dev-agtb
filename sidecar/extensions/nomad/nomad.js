(function(app) {
    app.augment("nomad", {

        deviceReady: function() {
            app.init({el: "#nomad" });
            app.logger.debug('App initialized');
            app.api.debug = app.config.debugSugarApi;
            app.start();
            app.logger.debug('App started');
        },

        /**
         * Displays email chooser UI.
         * @param {Array} emails
         * @param {String} subject(optional)
         * @param {String} body(optional)
         */
        sendEmail: function(emails, subject, body) {
            // TODO: Implement HTML action sheet view
        },

        /**
         * Displays phone chooser UI.
         * @param {Array} phones
         */
        callPhone: function(phones) {
            // TODO: Implement HTML action sheet view
        }

    });

})(SUGAR.App);