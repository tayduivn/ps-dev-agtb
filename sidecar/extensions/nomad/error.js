 // Nomad specific error handlers.
(function(app) {

    function _login() {
        app.router.login();
    }

    var _origHandleStatusCodesFallback = app.error.handleStatusCodesFallback;
    app.error = _.extend(app.error, {

        _alertUnauthorized: function() {
            app.alert.show("auth_error", {
                level: "error",
                messages: "Access not authorized.",
                autoClose: true
            });
        },

        _alertSystemError: function(code) {
            app.alert.show("system_error", {
                level: "error",
                messages: "Internal error (" + code + "). Please try again later.",
                autoClose: true
            });
        },

        // --------------------------------------------------------
        // OAuth2 errors
        // --------------------------------------------------------

        handleNeedsLoginError: function() {
            app.alert.show("auth_error", {
                level: "error",
                messages: "Invalid username or password",
                autoClose: true
            });
            _login();
        },

        handleInvalidGrantError: function() {
            app.alert.show("auth_error", {
                level: "error",
                messages: "Your session has been expired.",
                autoClose: true
            });
            _login();
        },

//        handleInvalidClientError:
//        handleUnauthorizedClientError:
//        handleInvalidRequestError:
//        handleUnsupportedGrantTypeError:
//        handleInvalidScopeError:

        // --------------------------------------------------------
        // Other errors
        // --------------------------------------------------------

        handleUnauthorizedError: function() {
            this._alertUnauthorized();
            _login();
        },

        handleForbiddenError: function() {
            this._alertUnauthorized();
            _login();
        },

        handleNotFoundError: function() {
            app.alert.show("not_found_error", {
                level: "error",
                messages: "Resource not found",
                autoClose: true
            });
        },

//        handleMethodNotAllowedError:
//        handleServerError:

        handleStatusCodesFallback: function(error) {
            app.alert.dismissAll();
            _origHandleStatusCodesFallback(error);
            if (error.textStatus == "timeout") {
                app.alert.show("system_error", {
                    level: "error",
                    messages: "Request timeout",
                    autoClose: true
                });
            }
            else {
                this._alertSystemError(error.status);
                if (error.status == "400") _login();
            }
        }


    });

})(SUGAR.App);

