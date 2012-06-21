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
     * @param {Object} xhr object
     * @param {String} error string 
     */
    app.error.handleNeedsLoginError = function(xhr, error) {
        backToLogin(true);
        app.alert.show("needs_login_error", {level: "error", messages: "The username/password combination provided is incorrect, please try again.", title:"Invalid Credentials", autoClose: true});
    };

    /**
     * This is caused by expired or invalid token. 
     * @param {Object} xhr object
     * @param {String} error string 
     */
    app.error.handleInvalidGrantError = function(xhr, error) {
        backToLogin(true);
        app.alert.show("invalid_grant_error", {level: "error", messages: "Your token is invalid or has expired. Please login again.", title:"Token Expired", autoClose: true});
    };

    /**
     * Client authentication handler. 
     * @param {Object} xhr object
     * @param {String} error string 
     */
    app.error.handleInvalidClientError = function(xhr, error) {
        backToLogin(true);
        app.alert.show("invalid_client_error", {level: "error", messages: "Client authentication failed.", title:"Invalid Client", autoClose: true});
    };
    /**
     * Invalid request handler. 
     * @param {Object} xhr object
     * @param {String} error string 
     */
    app.error.handleInvalidRequestError = function(xhr, error) {
        backToLogin(true);
        app.alert.show("invalid_request_error", {level: "error", messages: "The request made is invalid or malformed. Please contact technical support.", title:"Invalid Request", autoClose: true});
    };

    /**
     * 401 Unauthorized error handler. 
     * @param {Object} xhr object
     * @param {String} error string 
     */
    app.error.handleUnauthorizedError = function(xhr, error) {
        backToLogin(true);
        app.alert.show("unauthorized_request_error", {level: "error", messages: "We're sorry, but it appears you are unauthorized to access this resource.", title:"HTTP Error: 401 Unauthorized", autoClose: true});
    };

    /**
     * 403 Forbidden error handler. 
     * @param {Object} xhr object
     * @param {String} error string 
     */
    app.error.handleForbiddenError = function(xhr, error) {
        backToLogin(true);
        app.alert.show("forbidden_request_error", {level: "error", messages: "Resource not available.", title:"HTTP Error: 403 Forbidden", autoClose: true});
    };

    
    /**
     * 404 Not Found handler. 
     * @param {Object} xhr object
     * @param {String} error string 
     */
    app.error.handleNotFoundError = function(xhr, error) {
        app.router.navigate('error/404', {trigger: true});
    };

    /**
     * 405 Method not allowed handler.
     * @param {Object} xhr object
     * @param {String} error string 
     */
    app.error.handleMethodNotAllowedError = function(xhr, error) {
        backToLogin(true);
        app.alert.show("not_allowed_error", {level: "error", messages: "HTTP method not allowed for this resource. Please contact technical support.", title:"HTTP Error: 405 Method Not Allowed", autoClose: true});
    };

    /**
     * 412 Precondtion failure error.
     *
     * @param {Object} xhr object
     * @param {String} error string 
     */
    app.error.handlePreconditionFailureError = function(xhr, error) {
        backToLogin(true);
        // TODO: For finer grained control we could sniff the {error: <code>} in the response text (JSON) for one of:
        // missing_parameter, invalid_parameter, request_failure
        app.alert.show("precondtion_failure_error", {level: "error", messages: "Request failure, or, missing/invalid parameter. Please contact technical support", title:"HTTP Error: 412", autoClose: true});
    };
       
    /**
     * 500 Internal server error handler. 
     * @param {Object} xhr object
     * @param {String} error string 
     */
    app.error.handleServerError = function(xhr, error) {
        // Since we can get a 500 before app synced we 
        // may not have stared backbone history.
        if(!Backbone.History.started) {
            window.location.href = '#error/500';
            app.router.start();
        } else { 
            app.router.navigate('error/500', {trigger: true});
        }
    };

})(SUGAR.App);

