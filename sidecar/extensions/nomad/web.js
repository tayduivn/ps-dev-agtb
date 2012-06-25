(function(app) {

    // -------------------------------------------------
    // Private methods for pure web UI
    // -------------------------------------------------

    _.extend(app.nomad, {

        _showConfirm: function(message, confirmCallback, title, buttonLabels) {
            // TODO: Implement HTML modal dialog

            // Using standard browser confirm dialog for now
            // Mobile Safari displays buttons in the following order: 'Cancel', 'Confirm'
            // TODO: Test Android
            var confirmed = confirm(message);
            var index = confirmed ? 2 : 1;
            confirmCallback(index);
        },

        _showEmailComposer: function(subject, body, email) {
            app.logger.debug("Showing EMAIL composer for " + email);
        },

        _showSmsComposer: function(phone, message) {
            app.logger.debug("Showing SMS composer for phone " + phone);
        },

        _callPhone: function(phone) {
            app.logger.debug("Initiating a phone call to " + phone);
        },

        _browseUrl: function(url) {
            app.logger.debug("Opening URL in external browser: " + url);
        },

        _showActionSheet: function(title, items, callback) {
            app.logger.debug("Showing action sheet");
        }

    });


})(SUGAR.App);