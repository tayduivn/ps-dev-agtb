(function(app) {

     /**
     * Error handling module.
     * @class Core.Error
     * @singleton
     */
    var module = {
        refreshingLogin: false,
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
        // falls back to handleStatusCodesFallback. Caller ensures xhr.responseText exists.
        _callCustomHandler: function(xhr, textStatus, errorThrown, fn) {
            if (fn) {
                fn.call(this, xhr, textStatus, errorThrown);
            } else {
                this.handleStatusCodesFallback(xhr, textStatus, errorThrown);
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
         * @param xhr 
         * @param {String} textStatus
         * @param {String} errorThrown
         * @param {Function} alternativeCallback(optional) If this does not match an expected oauth error than this callback will be
         * called (if provided). 
         * @method
         */
        _handleFineGrainedError: function (xhr, textStatus, errorThrown, alternativeCallback) {
            var s = xhr && xhr.responseText ? JSON.parse(xhr.responseText) : "";
            var match = _.find(_.keys(this.statusCodes._customHandlersMap), function(oAuthCode) {
                return s.error == oAuthCode;
            });

            var handler = match ? this.statusCodes._customHandlersMap[match] : null;
            if (handler && this[handler]) {
                this[handler].call(this, xhr, textStatus, errorThrown);
            } else if (alternativeCallback) {
                this._callCustomHandler(xhr, textStatus, errorThrown, alternativeCallback);
            } else {
                this.handleStatusCodesFallback(xhr, textStatus, errorThrown)
            }
        },

        /**
         * Attempts to refresh the token.
         * @param originalXhr if refresh fails we want to use the original xhr and error (not xhr from refresh http call) 
         * @param originalTextStatus
         * @param originalErrorThrown
         * @param cb optional callback if refresh fails
         *
         */
        attemptRefresh: function(originalXhr, originalTextStatus, originalErrorThrown, cb) {
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
                        // Note that these are the xhr and error from original 401 since we don't want the xhr
                        // and error from the refresh http call!
                        cb(originalXhr, originalTextStatus, originalErrorThrown);
                    }
                };
                app.api.login({},{refresh:true},callbacks);
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
                "invalid_scope":           "handleInvalidScopeError",
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
            "400": function(xhr, textStatus, errorThrown) {
                this._handleFineGrainedError(xhr, textStatus, errorThrown);
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
            "401": function(xhr, textStatus, errorThrown) {
                var self = this, callbacks, s;

                // First check that it is not an error that we do NOT refresh on
                s = xhr && xhr.responseText ? JSON.parse(xhr.responseText) : "";
                if(s.error && $.inArray(s.error, self.statusCodes._dontAttemptRefresh) === -1) {

                    self.attemptRefresh(xhr, textStatus, errorThrown, function() {

                        // This callback will only be called if refresh fails so we fallback.
                        self._handleFineGrainedError(xhr, textStatus, errorThrown, self.handleUnauthorizedError);
                    });
                } else {
                    self._handleFineGrainedError(xhr, textStatus, errorThrown, self.handleUnauthorizedError);
                }
            },

            /**
             * Forbidden.
             *
             * Provide custom `handleForbiddenError` handler.
             * @method
             */
            "403": function(xhr, textStatus, errorThrown) {
                this._callCustomHandler(xhr, textStatus, errorThrown, this.handleForbiddenError);
            },

            /**
             * Not found.
             *
             * Provide custom `handleNotFoundError` handler.
             * @method
             */
            "404": function(xhr, textStatus, errorThrown) {
                this._callCustomHandler(xhr, textStatus, errorThrown, this.handleNotFoundError);
            },

            /**
             * Method not allowed.
             *
             * Provide custom `handleMethodNotAllowedError` handler.
             * @method
             */
            "405": function(xhr, textStatus, errorThrown) {
                this._callCustomHandler(xhr, textStatus, errorThrown, this.handleMethodNotAllowedError);
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
            "412": function(xhr, textStatus, errorThrown) {
                this._callCustomHandler(xhr, textStatus, errorThrown, this.handlePreconditionFailureError);
            },

            /**
             * Unprocessable Entity.
             *
             * Validation errors handled automatically.
             * @method
             */
            "422": function(xhr, textStatus, errorThrown, model) {
                this.handleValidationError(model, xhr.responseText);
            },

            /**
             * Internal server error.
             *
             * Provide custom `handleServerError` handler.
             * @method
             */
            "500": function(xhr, textStatus, errorThrown) {
                this._callCustomHandler(xhr, textStatus, errorThrown, this.handleServerError);
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
         * Handles http error codes returned from AJAX calls.
         *
         * See Jquery/Zepto documentation for details.
         * http://api.jquery.com/jQuery.ajax/
         * http://zeptojs.com/#$.ajax
         *
         * @param {XHR} xhr XMLHttpRequest object.
         * @param {String} textStatus String describing the type of error that occurred.
         * Possible values for the second argument (besides null) are `"timeout"`, `"error"`, `"abort"`, and `"parsererror"`.
         * @param {String} errorThrown Textual portion of the HTTP status, such as "Not Found" or "Internal Server Error."
         * when HTTP error occurs.
         * @param {Backbone.Model} model(optional) Instance of the model for which the request was made.
         * @member Core.Error
         */
        handleHttpError: function(xhr, textStatus, errorThrown, model) {
            // If we have a handler defined for this status code
            if(xhr) {
                if (xhr.status && this.statusCodes[xhr.status]) {
                    this.statusCodes[xhr.status].call(this, xhr, textStatus, errorThrown, model);
                } else {
                    // TODO: Default catch all error code handler
                    // Temporarily going to the handleStatusCodesFallback handler but will probably need
                    // to go to a sensible "all other errors" type of handler.
                    this.handleStatusCodesFallback(xhr, textStatus, errorThrown);
                }
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
            app.logger.error(mesg + " at " + url + " on line " + line);
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
         *        handleUnauthorizedError: function(xhr, textStatus, errorThrown) {
         *        },
         *
         *        ...
         *     });
         *
         * })(SUGAR.App);
         * </pre></code>
         * 
         * @param {XHR} xhr XMLHttpRequest object.
         * @param {String} textStatus String describing the type of error that occurred.
         * Possible values for the second argument (besides null) are `"timeout"`, `"error"`, `"abort"`, and `"parsererror"`.
         * @param {String} errorThrown Textual portion of the HTTP status, such as "Not Found" or "Internal Server Error."
         * when HTTP error occurs.
         * @member Core.Error
         */
        handleStatusCodesFallback: function(xhr, textStatus, errorThrown) {
            var message = "HTTP error: " + (xhr ? xhr.status : "(no-code)") +
                "\nResponse: " + (xhr ? xhr.responseText : "(empty-response)") +
                "\ntextStatus: " + textStatus +
                "\nerrorThrown: " + errorThrown;
            app.logger.error(message);
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

    // Enable error handling immediately.
    app.augment("error", module, module.initialize);

})(SUGAR.App);
