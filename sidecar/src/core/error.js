(function(app) {

     /**
     * Error handling module.
     * @class Core.Error
     * @singleton
     */
    var module = {
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
        _callCustomHandler: function(xhr, error, fn) {
            if (fn) {
                fn.call(this, xhr, error);
            } else {
                this.handleStatusCodesFallback(xhr, error);
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

            _authHandlerMap: {
                ".*invalid_grant.*":            "handleInvalidGrantError",
                ".*invalid_client.*":           "handleInvalidClientError",
                ".*invalid_request.*":          "handleInvalidRequestError",
                ".*unauthorized_client.*":      "handleUnauthorizedClientError",
                ".*unsupported_grant_type.*":   "handleUnsupportedGrantTypeError",
                ".*invalid_scope.*":            "handleInvalidScopeError"
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
             * was issued to another client.
             *
             * This happens when logging in with improper user/pass.
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
             * @method
             */
            "400": function(xhr, error) {

                var s = xhr && xhr.responseText ? xhr.responseText : "";
                var match = _.find(_.keys(this.statusCodes._authHandlerMap), function(regexStr) {
                    return (new RegExp(regexStr)).test(s);
                });

                var handler = match ? this.statusCodes._authHandlerMap[match] : null;
                if (handler && this[handler]) {
                    this[handler].call(this, xhr, error);
                }
                else {
                    this.handleStatusCodesFallback(xhr, error)
                }
            },

            /**
             * Unauthorized.
             *
             * Provide custom `handleUnauthorizedError` handler.
             * @method
             */
            "401": function(xhr, error) {
                this._callCustomHandler(xhr, error, this.handleUnauthorizedError);
            },

            /**
             * Forbidden.
             *
             * Provide custom `handleForbiddenError` handler.
             * @method
             */
            "403": function(xhr, error) {
                this._callCustomHandler(xhr, error, this.handleForbiddenError);
            },

            /**
             * Not found.
             *
             * Provide custom `handleNotFoundError` handler.
             * @method
             */
            "404": function(xhr, error) {
                this._callCustomHandler(xhr, error, this.handleNotFoundError);
            },

            /**
             * Method not allowed.
             *
             * Provide custom `handleMethodNotAllowedError` handler.
             * @method
             */
            "405": function(xhr, error) {
                this._callCustomHandler(xhr, error, this.handleMethodNotAllowedError);
            },

            /**
             * Unprocessable Entity.
             *
             * Validation errors handled automatically.
             * @method
             */
            "422": function(xhr, error, model) {
                this.handleValidationError(model, xhr.responseText);
            },

            /**
             * Internal server error.
             *
             * Provide custom `handleServerError` handler.
             * @method
             */
            "500": function(xhr, error) {
                this._callCustomHandler(xhr, error, this.handleServerError);
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
            compiledTemplate = Handlebars.compile(errorTemplate);

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
         * @param {XHR} xhr jQuery XHR Object
         * @param {String} error Error message
         * @member Core.Error
         */
        handleHttpError: function(xhr, error, model) {
            // If we have a handler defined for this status code
            if(xhr) {
                if (xhr.status && this.statusCodes[xhr.status]) {
                    this.statusCodes[xhr.status].call(this, xhr, error, model);
                } else {
                    // TODO: Default catch all error code handler
                    // Temporarily going to the handleStatusCodesFallback handler but will probably need
                    // to go to a sensible "all other errors" type of handler.
                    this.handleStatusCodesFallback(xhr, error);
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
         *        handleUnauthorizedError: function(xhr, error) {
         *        },
         *
         *        ...
         *     });
         *
         * })(SUGAR.App);
         * </pre></code>
         * 
         * @param {XHR} xhr
         * @param {String} error Error message
         * @member Core.Error
         */
        handleStatusCodesFallback: function(xhr, error) {
            var message = "HTTP error: " + (xhr ? xhr.statusCode : "(no-code)") +
                "\nResponse: " + (xhr ? xhr.responseText : "(empty-response)") +
                "\n" + error;
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
