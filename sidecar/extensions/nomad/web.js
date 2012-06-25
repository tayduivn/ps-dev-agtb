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
            window.location = "mailto:" + email +
                "?subject=" + (subject || "") +
                "&body=" + (body || "");
        },

        _showSmsComposer: function(phone, message) {
            window.location = "sms:" + phone;
        },

        _callPhone: function(phone) {
            window.location = "tel:" + phone;
        },

        _browseUrl: function(url) {
            window.open(url);
        },

        _showActionSheet: function(title, items, callback) {
            alert("Action sheet is not implemented!");
        }

    });


})(SUGAR.App);