/**
 * Portal specific error handlers.
 */
(function(app) {
    app.error = _.extend(app.error);

    function backToLogin(bDismiss) {
        if(bDismiss) app.alert.dismissAll();
        app.router.login();
    }
    
    /**
     * This is caused by attempt to login with invalid creds. 
     */
    app.error.handleNeedLoginError = function(error) {
        backToLogin(true);
        app.alert.show("needs_login_error", {level: "error", messages: "The username/password combination provided is incorrect, please try again.", title:"Invalid Credentials", autoClose: true});
    };

    /**
     * This is caused by expired or invalid token. 
     */
    app.error.handleInvalidGrantError = function(error) {
        backToLogin(true);
        app.alert.show("invalid_grant_error", {level: "error", messages: "Your token is invalid or has expired. Please login again.", title:"Token Expired", autoClose: true});
    };

    /**
     * Client authentication handler. 
     */
    app.error.handleInvalidClientError = function(error) {
        backToLogin(true);
        app.alert.show("invalid_client_error", {level: "error", messages: "Client authentication failed.", title:"Invalid Client", autoClose: true});
    };
    /**
     * Invalid request handler. 
     */
    app.error.handleInvalidRequestError = function(error) {
        backToLogin(true);
        app.alert.show("invalid_request_error", {level: "error", messages: "The request made is invalid or malformed. Please contact technical support.", title:"Invalid Request", autoClose: true});
    };

    /**
     * 0 Timeout error handler. If server doesn't respond within timeout.
     */
    app.error.handleTimeoutError = function(error) {
        backToLogin(true);
        app.alert.show("timeout_error", {level: "error", messages: "The request timed out.", title:"Request timeout", autoClose: true});
    };

    /**
     * 401 Unauthorized error handler. 
     */
    app.error.handleUnauthorizedError = function(error) {
        backToLogin(true);
        app.alert.show("unauthorized_request_error", {level: "error", messages: "We're sorry, but it appears you are unauthorized to access this resource.", title:"HTTP Error: 401 Unauthorized", autoClose: true});
    };

    /**
     * 403 Forbidden error handler. 
     */
    app.error.handleForbiddenError = function(error) {
        app.alert.show("forbidden_request_error", {level: "error", messages: "Resource not available.", title:"HTTP Error: 403 Forbidden", autoClose: true});
    };
    
    /**
     * 404 Not Found handler. 
     */
    app.error.handleNotFoundError = function(error) {
        app.controller.loadView({
            layout: "error",
            errorType: "404",
            module: "Error",
            create: true
        });    
    };

    /**
     * 405 Method not allowed handler.
     */
    app.error.handleMethodNotAllowedError = function(error) {
        backToLogin(true);
        app.alert.show("not_allowed_error", {level: "error", messages: "HTTP method not allowed for this resource. Please contact technical support.", title:"HTTP Error: 405 Method Not Allowed", autoClose: true});
    };

    /**
     * 412 Precondtion failure error.
     */
    app.error.handlePreconditionFailureError = function(error) {
        backToLogin(true);
        // TODO: For finer grained control we could sniff the {error: <code>} in the response text (JSON) for one of:
        // missing_parameter, invalid_parameter, request_failure
        app.alert.show("precondtion_failure_error", {level: "error", messages: "Request failure, or, missing/invalid parameter. Please contact technical support", title:"HTTP Error: 412", autoClose: true});
    };
       
    /**
     * 500 Internal server error handler. 
     */
    app.error.handleServerError = function(error) {
        app.controller.loadView({
            layout: "error",
            errorType: "500",
            module: "Error",
            create: true
        });
    };

})(SUGAR.App);

