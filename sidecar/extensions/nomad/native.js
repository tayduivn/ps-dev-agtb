(function(app) {

    _.extend(app.nomad, {

        _showEmailComposer: function(subject, body, email) {
            window.plugins.emailComposer.showEmailComposer(subject, body, email, null, null, false);
        },

        _showSmsComposer: function(phone, message) {
            app.logger.debug("Showing SMS composer for phone " + phone);
            window.plugins.smsComposer.showSMSComposer(phone, message);
        },

        _callPhone: function(phone) {
            app.logger.debug("Initiating a phone call to " + phone);
            window.plugins.phonedialer.dial(phone);
        },

        _browseUrl: function(url) {
            app.logger.debug("Opening URL in external browser: " + url);
            window.plugins.browseURL.browse(url);
        },

        _showActionSheet: function(title, items, callback) {
            // TODO: Localize cancel
            window.plugins.actionSheet.create(title, items.concat(["Cancel"]), callback, { cancelButtonIndex: items.length });
        },

        _showConfirm: function(message, confirmCallback, title, buttonLabels) {
            navigator.notification.confirm(message, confirmCallback, title, buttonLabels);
        }

    });

})(SUGAR.App);