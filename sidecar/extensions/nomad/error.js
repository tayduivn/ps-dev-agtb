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

        handleStatusCodesFallback: function(xhr, textStatus, errorThrown) {
            app.alert.dismissAll();
            _origHandleStatusCodesFallback(xhr, textStatus, errorThrown);
            if (textStatus == "timeout") {
                app.alert.show("system_error", {
                    level: "error",
                    messages: "Request timeout",
                    autoClose: true
                });
            }
            else {
                var code = xhr ? xhr.status : "N/A";
                this._alertSystemError(code);
                if (code == "400") _login();
            }
        }


    });

})(SUGAR.App);

