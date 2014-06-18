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
 * SugarCRM error handlers.
 */
(function(app) {
    app.error = _.extend(app.error);

    function backToLogin(bDismiss) {
        if(bDismiss) app.alert.dismissAll();
        app.router.login();
    }

    function showErrorPage(status, dismiss) {
        if(dismiss) {
            app.alert.dismissAll();
        }

        app.controller.loadView({
           layout: "error",
           errorType: status,
           module: "Error",
           create: true
       });
    }

    function alertUser(key,title,msg) {
        app.alert.show(key, {
            level: 'error',
            messages: app.lang.getAppString(msg),
            title: app.lang.get(title)
        });
    }
    
    /**
     * This is caused by attempt to login with invalid creds. 
     */
    app.error.handleNeedLoginError = function(error) {
        backToLogin(true);
        // Login can fail for many reasons such as lock out, bad credentials, etc.  Server message to provides details.
        alertUser("needs_login_error" , "LBL_INVALID_CREDS_TITLE", error.message);
    };

    /**
     * This is caused by expired or invalid token. 
     */
    app.error.handleInvalidGrantError = function(error) {
        backToLogin(true);
        alertUser("invalid_grant_error", "LBL_INVALID_GRANT_TITLE", "LBL_INVALID_GRANT");
    };

    /**
     * Client authentication handler. 
     */
    app.error.handleInvalidClientError = function(error) {
        backToLogin(true);
        alertUser("invalid_client_error","LBL_AUTH_FAILED_TITLE","LBL_AUTH_FAILED");
    };
    
    /**
     * Invalid request handler. 
     */
    app.error.handleInvalidRequestError = function(error) {
        backToLogin(true);
        alertUser("invalid_request_error", "LBL_INVALID_REQUEST_TITLE", "LBL_INVALID_REQUEST");
    };

    /**
     * 0 Timeout error handler. If server doesn't respond within timeout.
     */
    app.error.handleTimeoutError = function(error) {
        app.alert.dismissAll();
        alertUser("timeout_error", "LBL_REQUEST_TIMEOUT_TITLE", "LBL_REQUEST_TIMEOUT");
    };

    /**
     * 401 Unauthorized error handler. 
     */
    app.error.handleUnauthorizedError = function(error) {
        backToLogin(true);
        alertUser("unauthorized_request_error", "LBL_UNAUTHORIZED_TITLE", "LBL_UNAUTHORIZED");
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
        app.logger.error(app.lang.get(error.message ? error.message : "LBL_RESOURCE_UNAVAILABLE"));
    };
    
    /**
     * 404 Not Found handler.
     * If a model triggered the 404 but the model did not belong to the master layout,
     * this function will not handle that error.
     * Those errors should be handled by listeners on the model/collection and the views that
     * requested the data.
     */
    app.error.handleNotFoundError = function(error, model, options) {
        var layout = app.controller.layout || {};
        if ((options && options.context != layout.context)
            || (model && layout.context && layout.context.get("model") && layout.context.get("model") != model)
        ) {
            return;
        }
        if (!layout ||
            !_.isObject(layout.error) ||
            !_.isFunction(layout.error.handleNotFoundError) ||
            layout.error.handleNotFoundError(error, model, options) !== false
        ) {
            showErrorPage("404");
        }
    };

    /**
     * 405 Method not allowed handler.
     */
    app.error.handleMethodNotAllowedError = function(error) {
        backToLogin(true);
        alertUser("not_allowed_error", "LBL_METHOD_NOT_ALLOWED_TITLE", "LBL_METHOD_NOT_ALLOWED");
    };

    /**
     * 409 Handle conflict error.
     */
    app.error.handleMethodConflictError = function(error) {
        app.logger.error('Data conflict detected.');
    };

    /**
     * 422 Handle validation error
     */
    app.error.handleValidationError = function(error) {
        var layout = app.controller.layout;
        if( !_.isObject(layout.error) ||
            !_.isFunction(layout.error.handleValidationError) ||
            layout.error.handleValidationError(error) !== false
        ) {
            //Ignore errors triggered from models, they should be handled by the views.
            if (error instanceof app.data.beanModel) {
                return;
            }
            alertUser("validation_error", "LBL_PRECONDITION_MISSING_TITLE", error.message || "LBL_PRECONDITION_MISSING");
            error.handled = true;
        }
    };

    /**
     * 412 Header precondition failure error.
     */
    app.error.handleHeaderPreconditionFailed = function(error, b, c, d) {
        //Only kick off a sync if we are not already in the process of syncing
        if (error && error.code ==='metadata_out_of_date' && app.isSynced) {
            app.sync();
        }
    };

    /**
     * 424 Method failure error.
     */
    app.error.handleMethodFailureError = function(error) {
        // TODO: For finer grained control we could sniff the {error: <code>} in the response text (JSON) for one of:
        // missing_parameter, invalid_parameter, request_failure
        error.handled = true;
        if (error.code == "request_failure") {
            showErrorPage("422");
        } else {
            alertUser("precondtion_failure_error", "LBL_PRECONDITION_MISSING_TITLE", "LBL_PRECONDITION_MISSING");
        }
    };
       
    /**
     * 500 Internal server error handler. 
     */
    app.error.handleServerError = function(error) {
        if(error.payload.url) {
            // Redirect admins instead of loading the error view.
            if (app.acl.hasAccess('admin','Administration')) {
                app.router.navigate(error.payload.url,{trigger: true, replace: true});
                return;
            }
        }
        app.controller.loadView({
            layout: "error",
            errorType: error.status || "500",
            module: "Error",
            error: error, 
            create: true
        });
    };

})(SUGAR.App);

