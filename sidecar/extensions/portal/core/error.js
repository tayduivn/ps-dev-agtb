/**
 * Portal specific error handlers.
 */
(function(app) {
    app.error = _.extend(app.error);

    function backToLogin(bDismiss) {
        if(bDismiss) app.alert.dismissAll();
        app.api.logout();
        app.router.login();
    }

    /**
     * This is caused by invalid user creds. 
     * @param {Object} xhr object
     * @param {String} error string 
     */
    app.error.handleInvalidGrantError = function(xhr, error) {
        backToLogin(true);
        app.alert.show("invalid_grant_error", {level: "error", messages: "The username/password combination provided is incorrect, please try again.", title:"Invalid Credentials", autoClose: true});
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
        app.alert.show("invalid_request_error", {level: "error", messages: "We're sorry, but it appears you are unauthorized to access this resource.", title:"HTTP Error: 401 Unauthorized", autoClose: true});
    };

    /**
     * 403 Forbidden error handler. 
     * @param {Object} xhr object
     * @param {String} error string 
     */
    app.error.handleForbiddenError = function(xhr, error) {
        backToLogin(true);
        app.alert.show("invalid_request_error", {level: "error", messages: "Resource not available.", title:"HTTP Error: 403 Forbidden", autoClose: true});
    };

    
    /**
     * 404 Not Found handler. 
     * @param {Object} xhr object
     * @param {String} error string 
     */
    app.error.handleNotFoundError = function(xhr, error) {
        // TODO - redirect to 404 page
        app.router.navigate('error/404', {trigger: true});
    };

    /**
     * 405 Method not allowed handler.
     * @param {Object} xhr object
     * @param {String} error string 
     */
    app.error.handleMethodNotAllowedError = function(xhr, error) {
        backToLogin(true);
        app.alert.show("invalid_request_error", {level: "error", messages: "HTTP method not allowed for this resource. Please contact technical support.", title:"HTTP Error: 405 Method Not Allowed", autoClose: true});
    };

    /**
     * 500 Internal Server error handler.
     * @param {Object} xhr object
     * @param {String} error string 
     */
    app.error.handleMethodNotAllowedError = function(xhr, error) {
        // TODO - redirect to 500 page
        app.router.navigate('error/500', {trigger: true});
    };
})(SUGAR.App);

