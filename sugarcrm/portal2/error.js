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

    function alertUser(key,title,msg) {
        app.alert.show(key, {level: "error", messages: app.lang.getAppString(msg), title:app.lang.get(title), autoClose: true});
    }
    
    /**
     * This is caused by attempt to login with invalid creds. 
     */
    app.error.handleNeedLoginError = function(error) {
        backToLogin(true);
        alertUser("needs_login_error" , "LBL_PORTAL_INVALID_CREDS_TITLE", "LBL_PORTAL_INVALID_CREDS");
    };

    /**
     * This is caused by expired or invalid token. 
     */
    app.error.handleInvalidGrantError = function(error) {
        backToLogin(true);
        alertUser("invalid_grant_error", "LBL_PORTAL_INVALID_GRANT_TITLE", "LBL_PORTAL_INVALID_GRANT");
    };

    /**
     * Client authentication handler. 
     */
    app.error.handleInvalidClientError = function(error) {
        backToLogin(true);
        alertUser("invalid_client_error","LBL_PORTAL_AUTH_FAILED_TITLE","LBL_PORTAL_AUTH_FAILED");
    };
    
    /**
     * Invalid request handler. 
     */
    app.error.handleInvalidRequestError = function(error) {
        backToLogin(true);
        alertUser("invalid_request_error", "LBL_PORTAL_INVALID_REQUEST_TITLE", "LBL_PORTAL_INVALID_REQUEST");
    };

    /**
     * 0 Timeout error handler. If server doesn't respond within timeout.
     */
    app.error.handleTimeoutError = function(error) {
        backToLogin(true);
        alertUser("timeout_error", "LBL_PORTAL_REQUEST_TIMEOUT_TITLE", "LBL_PORTAL_REQUEST_TIMEOUT");
    };

    /**
     * 401 Unauthorized error handler. 
     */
    app.error.handleUnauthorizedError = function(error) {
        backToLogin(true);
        alertUser("unauthorized_request_error", "LBL_PORTAL_UNAUTHORIZED_TITLE", "LBL_PORTAL_UNAUTHORIZED");
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
        alertUser("forbidden_request_error", "LBL_PORTAL_RESOURCE_UNAVAILABLE_TITLE", error.message ? error.message : "LBL_PORTAL_RESOURCE_UNAVAILABLE");
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
        alertUser("not_allowed_error", "LBL_PORTAL_METHOD_NOT_ALLOWED_TITLE", "LBL_PORTAL_METHOD_NOT_ALLOWED");
    };

    /**
     * 412 Header precondition failure error.
     */
    app.error.handleHeaderPreconditionFailed = function(error) {
        app.sync();
    };

    /**
     * 422 Method failure error.
     */
    app.error.handleMethodFailureError = function(error) {
        backToLogin(true);
        // TODO: For finer grained control we could sniff the {error: <code>} in the response text (JSON) for one of:
        // missing_parameter, invalid_parameter, request_failure
        alertUser("precondtion_failure_error", "LBL_PORTAL_PRECONDITION_MISSING_TITLE", "LBL_PORTAL_PRECONDITION_MISSING");
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

