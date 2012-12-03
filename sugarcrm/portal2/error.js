/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
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
        app.alert.show("needs_login_error", {level: "error", messages: "LBL_PORTAL_INVALID_CREDS", title:"LBL_PORTAL_INVALID_CREDS_TITLE", autoClose: true});
    };

    /**
     * This is caused by expired or invalid token. 
     */
    app.error.handleInvalidGrantError = function(error) {
        backToLogin(true);
        app.alert.show("invalid_grant_error", {level: "error", messages: "LBL_PORTAL_INVALID_GRANT", title:"LBL_PORTAL_INVALID_GRANT_TITLE", autoClose: true});
    };

    /**
     * Client authentication handler. 
     */
    app.error.handleInvalidClientError = function(error) {
        backToLogin(true);
        app.alert.show("invalid_client_error", {level: "error", messages: "LBL_PORTAL_AUTH_FAILED", title:"LBL_PORTAL_AUTH_FAILED_TITLE", autoClose: true});
    };
    
    /**
     * Invalid request handler. 
     */
    app.error.handleInvalidRequestError = function(error) {
        backToLogin(true);
        app.alert.show("invalid_request_error", {level: "error", messages: "LBL_PORTAL_INVALID_REQUEST", title:"LBL_PORTAL_INVALID_REQUEST_TITLE", autoClose: true});
    };

    /**
     * 0 Timeout error handler. If server doesn't respond within timeout.
     */
    app.error.handleTimeoutError = function(error) {
        backToLogin(true);
        app.alert.show("timeout_error", {level: "error", messages: "LBL_PORTAL_REQUEST_TIMEOUT", title:"LBL_PORTAL_REQUEST_TIMEOUT_TITLE", autoClose: true});
    };

    /**
     * 401 Unauthorized error handler. 
     */
    app.error.handleUnauthorizedError = function(error) {
        backToLogin(true);
        app.alert.show("unauthorized_request_error", {level: "error", messages: "LBL_PORTAL_UNAUTHORIZED", title:"LBL_PORTAL_UNAUTHORIZED_TITLE", autoClose: true});
    };

    /**
     * 403 Forbidden error handler. 
     */
    app.error.handleForbiddenError = function(error) {
        app.alert.dismissAll();
        // If portal is not configured, return to login screen if necessary
        if(error.code == "portal_not_configured"){
            backToLogin(true);
        }
        app.alert.show("forbidden_request_error", {level: "error", messages: error.message ? error.message : "LBL_PORTAL_RESOURCE_UNAVAILABLE", title:"LBL_PORTAL_RESOURCE_UNAVAILABLE_TITLE", autoClose: true});
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
        app.alert.show("not_allowed_error", {level: "error", messages: "LBL_PORTAL_METHOD_NOT_ALLOWED", title:"LBL_PORTAL_METHOD_NOT_ALLOWED_TITLE", autoClose: true});
    };

    /**
     * 412 Precondtion failure error.
     */
    app.error.handlePreconditionFailureError = function(error) {
        backToLogin(true);
        // TODO: For finer grained control we could sniff the {error: <code>} in the response text (JSON) for one of:
        // missing_parameter, invalid_parameter, request_failure
        app.alert.show("precondtion_failure_error", {level: "error", messages: "LBL_PORTAL_PRECONDITION_MISSING", title:"LBL_PORTAL_PRECONDITION_MISSING_TITLE", autoClose: true});
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

