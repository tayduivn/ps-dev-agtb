 // Nomad specific error handlers.
(function(app) {

    function _login() {
        app.router.login();
    }

    app.error = _.extend(app.error, {

        _alertUnauthorized: function() {
            app.alert.show("auth_error", {
                level: "error",
                messages: "Access not authorized.",
                autoClose: true
            });
        },

        _alertSystemError: function() {
            app.alert.show("system_error", {
                level: "error",
                messages: "Internal error. Please try again later.",
                autoClose: true
            });
        },

        // --------------------------------------------------------
        // OAuth2 errors
        // --------------------------------------------------------

        handleInvalidGrantError: function(xhr, error) {
            app.alert.show("auth_error", {
                level: "error",
                messages: "Invalid username or password",
                autoClose: true
            });
            _login();
        },

        // This is actually invalid client ID (app ID) -- it shouldn't ever happen
        handleInvalidClientError: function(xhr, error) {
            this._alertUnauthorized();
            _login();
        },

        handleUnauthorizedClientError: function(xhr, error) {
            this._alertUnauthorized(xhr, error);
            _login();
        },

        handleInvalidRequestError: function(xhr, error) {
            this._alertSystemError();
            _login();
        },

        handleUnsupportedGrantTypeError: function(xhr, error) {
            this._alertSystemError();
            _login();
        },

        handleInvalidScopeError: function(xhr, error) {
            this._alertSystemError();
            _login();
        },

        // --------------------------------------------------------
        // Other errors
        // --------------------------------------------------------

        handleUnauthorizedError: function(xhr, error) {
            this._alertUnauthorized();
            _login();
        },

        handleForbiddenError: function(xhr, error) {
            this._alertUnauthorized();
            _login();
        },

        handleNotFoundError: function(xhr, error) {
            app.alert.show("not_found_error", {
                level: "error",
                messages: "Resource not found",
                autoClose: true
            });
        },

        handleMethodNotAllowedError: function(xhr, error) {
            app.logger.warn("Server error: " + error);
            this._alertSystemError();
        },

        handleServerError: function(xhr, error) {
            app.logger.warn("Server error: " + error);
            this._alertSystemError();
        }

    });

})(SUGAR.App);

