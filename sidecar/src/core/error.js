(function(app) {

     /**
     * Error handling module.
     * @class Core.Error
     * @singleton
     */
    var module = {
        refreshingLogin: false,

        init: function() {
            this.initialize();
        },

        /**
         * Setups the params for error module
         * @param opts
         */
        initialize: function(opts) {
            opts = opts || {};

            /**
             * Set to true to enable remote logging to server [NOT IMPLEMENTED]
             * @cfg {Boolean}
             */
            this.remoteLogging = opts.remoteLogging || false;

            /**
             * Inject a hash of status code handlers to override defaults
             * @cfg {Object}
             */
            this.statusCodes = (opts.statusCodes) ? _.extend(this.statusCodes, opts.statusCodes) : this.statusCodes;

            /**
             * Set to true to disable onError overloading
             * @cfg {Boolean} disableOnError
             */
            if (!opts.disableOnError) {
                this.enableOnError();
            }
        },

        // This attempts to call function fn (which may not exist), otherwise,
        // falls back to handleStatusCodesFallback.
        _callCustomHandler: function(error, fn) {
            if (fn) {
                fn.call(this, error);
            } else {
                this.handleStatusCodesFallback(error);
            }
        },
    
        /**
         * Authentication error.
         *
         * OAuth2 uses 400 as a sort of catch all; see:
         * http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-5.2
         *
         * Provide the following custom handlers:
         *
         * **handleInvalidGrantError**
         *
         * The provided authorization grant is invalid, expired, revoked, does
         * not match the redirection URI used in the authorization request, or
         * was issued to another client. Note that the server implementation
         * will override invalid_grant as needs_login as a special case (see below).
         *
         * **handleNeedsLoginError**
         *
         * The server shall use this in place of invalid_grant to tell client to handle 
         * error specifically as caused due to invalid credentials being supplied. The 
         * reason server needs to use this is because an invalid_grant oauth error may 
         * also be caused by invalid or expired token. Using needs_login allows all 
         * clients to provide proper messaging to end user without the need for extra logic.
         *
         * **handleInvalidClientError**
         *
         * Client authentication failed (e.g. unknown client, no client
         * authentication included, multiple client authentications included,
         * or unsupported authentication method).
         *
         * **handleInvalidRequestError**
         *
         * The request is missing a required parameter, includes an unsupported
         * parameter or parameter value, repeats a parameter, includes multiple
         * credentials, utilizes more than one mechanism for authenticating the
         * client, or is otherwise malformed.
         *
         * **handleUnauthorizedClientError**
         *
         * The authenticated client is not authorized to use this authorization grant type.
         *
         * **handleUnsupportedGrantTypeError**
         *
         * The authorization grant type is not supported by the authorization server.
         *
         * **handleInvalidScopeError**
         *
         * The requested scope is invalid, unknown, malformed, or exceeds the scope granted by the resource owner.
         *
         *
         * @param {SUGAR.HttpError} error
         * @param {Function} alternativeCallback(optional) If this does not match an expected oauth error than this callback will be
         * called (if provided). 
         * @method
         * @private
         */
        _handleFineGrainedError: function(error, alternativeCallback) {
            var oauthError = this._extractOAuthError(error.responseText);
            var match = _.find(_.keys(this.statusCodes._customHandlersMap), function(oAuthCode) {
                return oauthError == oAuthCode;
            });

            var handler = match ? this.statusCodes._customHandlersMap[match] : null;
            if (handler && this[handler]) {
                this[handler].call(this, error);
            } else if (alternativeCallback) {
                this._callCustomHandler(error, alternativeCallback);
            } else {
                this.handleStatusCodesFallback(error);
            }
        },

         // Extracts OAuth error from response
        _extractOAuthError: function(response) {
            var s;
            if (response) {
                try {
                    s = JSON.parse(response);
                }
                catch(e) {
                    app.logger.error("Failed to parse OAuth response: " + response + "\n" + e);
                }
            }

            return s ? s.error : null;
        },

        /**
         * Attempts to refresh the token.
         * @param {SUGAR.HttpError} originalError if refresh fails we want to use the original error (not error from refresh http call)
         * @param cb optional callback if refresh fails
         *
         */
        attemptRefresh: function(originalError, cb) {
            var callbacks, self = this;

            if (!this.refreshingLogin) {
                this.refreshingLogin = true;

                callbacks = {
                    success: function(){
                        self.refreshingLogin = false;
                        app.router.start();
                    },
                    error: function(){
                        self.refreshingLogin = false;
                        // Note that this is error from original 401
                        // since we don't want the error from the refresh http call!
                        cb(originalError);
                    }
                };
                app.api.login({}, {refresh:true}, callbacks);
            }
        },

        /**
         * An object of status code error handlers. If custom handler is defined by extending
         * module, corresponding status code handler will attemp to use that, otherwise,
         * handleStatusCodesFallback is used as a fallback just logging the error.
         * @class Core.Error.statusCodes
         * @singleton
         * @member Core.Error
         */
        statusCodes: {

            _customHandlersMap: {
                "invalid_grant":           "handleInvalidGrantError",
                "need_login":              "handleNeedsLoginError",
                "invalid_client":          "handleInvalidClientError",
                "invalid_request":         "handleInvalidRequestError",
                "unauthorized_client":     "handleUnauthorizedClientError",
                "unsupported_grant_type":  "handleUnsupportedGrantTypeError",
                "invalid_scope":           "handleInvalidScopeError"
                /* TODO: Add any other oauth or custom codes we care about here */
            },

            // Unless one of these, 401's will always attempt to refresh token before falling back to generic 401 handling.
            _dontAttemptRefresh: ["invalid_client", "invalid_request", "unauthorized_client", "unsupported_grant_type", "invalid_scope"],
            
            /**
             * Authentication error.
             *
             * Since oauth server implementation might throw 401 (as well as 400)
             * we route this to the handleOAuthError. If no match for oauth error
             * than handleOAuthError will try to use handleUnauthorizedError if 
             * supplied.
             *
             * Provide custom `handleUnauthorizedError` handler.
             * @method
             */
            "400": function(error) {
                this._handleFineGrainedError(error);
            },

            /**
             * Unauthorized.
             *
             * Since oauth server implementation might throw 401 (as well as 400)
             * we route this to the handleOAuthError. If no match for oauth error
             * than handleOAuthError will try to use handleUnauthorizedError if 
             * supplied.
             *
             * Provide custom `handleUnauthorizedError` handler.
             * @method
             */
            "401": function(error) {
                var self = this, oauthError;

                // First check that it is not an error that we do NOT refresh on
                oauthError = this._extractOAuthError(error.responseText);
                if (oauthError && $.inArray(oauthError, self.statusCodes._dontAttemptRefresh) === -1) {

                    self.attemptRefresh(error, function() {

                        // This callback will only be called if refresh fails so we fallback.
                        self._handleFineGrainedError(error, self.handleUnauthorizedError);
                    });
                } else {
                    self._handleFineGrainedError(error, self.handleUnauthorizedError);
                }
            },

            /**
             * Forbidden.
             *
             * Provide custom `handleForbiddenError` handler.
             * @method
             */
            "403": function(error) {
                this._callCustomHandler(error, this.handleForbiddenError);
            },

            /**
             * Not found.
             *
             * Provide custom `handleNotFoundError` handler.
             * @method
             */
            "404": function(error) {
                this._callCustomHandler(error, this.handleNotFoundError);
            },

            /**
             * Method not allowed.
             *
             * Provide custom `handleMethodNotAllowedError` handler.
             * @method
             */
            "405": function(error) {
                this._callCustomHandler(error, this.handleMethodNotAllowedError);
            },

            /**
             * Precondition failure.
             *
             * Clients can optionally sniff the error property in JSON for finer grained 
             * determination; the following values may be:
             * missing_parameter, invalid_parameter, request_failure
             *
             * Provide custom `handlePreconditionFailureError` handler.
             * @method
             */
            "412": function(error) {
                this._callCustomHandler(error, this.handlePreconditionFailureError);
            },

            /**
             * Unprocessable Entity.
             *
             * Validation errors handled automatically.
             * @method
             */
            "422": function(error, model) {
                this.handleValidationError(model, error.responseText);
            },

            /**
             * Internal server error.
             *
             * Provide custom `handleServerError` handler.
             * @method
             */
            "500": function(error) {
                this._callCustomHandler(error, this.handleServerError);
            }
        },

        remoteLogging: false,

        /**
         * Returns error strings given a error key and context
         * @param errorKey
         * @param context
         * @member Core.Error
         */
        getErrorString: function(errorKey, context) {
            var errorName2Keys, module, errorTemplate, compiledTemplate;
            errorName2Keys = {
                "maxLength":"ERROR_MAX_FIELD_LENGTH",
                "minLength":"ERROR_MIN_FIELD_LENGTH",
                "required":"ERROR_FIELD_REQUIRED",
                "email":"ERROR_EMAIL"
            };
            module = context.module || '';
            errorTemplate = app.lang.get(errorName2Keys[errorKey] || errorKey, module);
            compiledTemplate = app.template.compile(errorName2Keys[errorKey] || 'error_' + errorKey, errorTemplate);

            return compiledTemplate(context);
        },

        /**
         * Handles validation errors. By default this just pipes the error to the
         * error logger.
         * @param {Data.Bean} model Model in which validation failed
         * @param {Object} errors Hash of fields that failed
         * @member Core.Error
         */
        handleValidationError: function(model, errors) {
            // TODO: Right now doesn't stringify the error, add it in when we finalize the
            // structure of the error.

            // TODO: Likely, we'll have a 'Saving...' alert, etc., and so we just dismiss all
            // since we don't know the alert key. Ostensibly, validation errors will show
            // field by field; so feedback will be provided as appropriate.
            app.alert.dismissAll();

            _.each(errors, function(fieldError, key) {
                var errorMsg = '';
                if (_.isObject(fieldError)) {
                    _.each(fieldError, function(result, fieldName) {
                        errorMsg +=  "(Message) " + this.getErrorString(fieldName, model) + "\n";
                    }, this);
                } else {
                    errorMsg = fieldError;
                }
                app.logger.debug("validation failed for field `" + key + "`:\n" + errorMsg);
            }, this);
        },

        /**
         * Handles http errors returned from AJAX calls.
         * @param {SUGAR.HttpError} error AJAX error.
         * @param {Backbone.Model} model(optional) Instance of the model for which the request was made.
         * @member Core.Error
         */
        handleHttpError: function(error, model) {
            // If we have a handler defined for this status code
            if (this.statusCodes[error.status]) {
                this.statusCodes[error.status].call(this, error, model);
            } else {
                // TODO: Default catch all error code handler
                // Temporarily going to the handleStatusCodesFallback handler but will probably need
                // to go to a sensible "all other errors" type of handler.
                this.handleStatusCodesFallback(error);
            }
        },

        /**
         * This is the default error handler we overload onerror with
         * @param {String} mesg Error message
         * @param {String} url URL of script
         * @param {String} line Line number of script
         * @member Core.Error
         */
        handleError: function(mesg, url, line) {
            app.logger.fatal(mesg + " at " + url + " on line " + line);
        },
        
        /**
         * This is the fallback error handler if custom status code specific handler
         * not provided in application specific error handler. To define custom error
         * handlers, you should include your script from index page and do something like:
         * <pre><code>
         * (function(app) {
         *
         *     app.error = _.extend(app.error, {
         *        // put your custom handlers here.
         *        handleUnauthorizedError: function(error) {
         *        },
         *
         *        ...
         *     });
         *
         * })(SUGAR.App);
         * </pre></code>
         * 
         * @param {String} error Ajax error.
         * @member Core.Error
         */
        handleStatusCodesFallback: function(error) {
            app.logger.error(error.toString());
        },

        /**
         * Overloads the window.onerror catch all function. Calls the original if any while
         * adding the framework's custom error handling logic. Pass in a custom callback to
         * add additional error handling.
         * @param {Function} handler Callback function to call on error.
         * @param {Object} context Scope of the callback
         * @return {Boolean} False if onerror has already been overloaded.
         * @member Core.Error
         */
        enableOnError: function(handler, context) {
            var originalHandler,
                self = this;

            if (this.overloaded) {
                return false;
            }

            originalHandler = window.onerror;

            window.onerror = function(mesg, url, line) {
                if (handler) {
                    handler.call(context);
                } else {
                    self.handleError(mesg, url, line);
                }

                if (originalHandler) {
                    originalHandler();
                }
            };

            this.overloaded = true;

            return true;
        }
    };

    // We don't want to initialize error handling immediately,
    // because the handler may use code that have not been initialized yet
    app.augment("error", module, false);

})(SUGAR.App);
