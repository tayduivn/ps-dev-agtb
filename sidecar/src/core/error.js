(function(app) {

    function getGenericMessage(statusCode, error) {
        return "Error in "+statusCode+" handler, but no xhr.responseText available. "+(error?'Error: '+error:'');
    }

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
        callCustomHandlerIfXhr: function(xhr, error, status, fn) {
            if(xhr && xhr.responseText) {
                this.callCustomHandler(xhr, error, fn);
            }  else {
                this.handleStatusCodesFallback(getGenericMessage(status, error));
            }
        },

        // This attempts to call function fn (which may not exist), otherwise,
        // falls back to handleStatusCodesFallback. Caller ensures xhr.responseText exists.
        callCustomHandler: function(xhr, error, fn) {
            if(fn) {
                fn(xhr, error);
            } else {
                this.handleStatusCodesFallback(xhr.responseText);
            }
        },
        /**
         * Attempts to match on regexStr and delegate to corresponding handler. Otherwise,
         * resorts to calling handleStatusCodesFallback fallback.
         */
        callCustomIfMatchingError: function(xhr, error, regexStr, statusCode, fn) {
            var re = new RegExp(regexStr); 
            if(xhr && xhr.responseText && re.test(xhr.responseText)) {
                this.callCustomHandler(xhr, error, fn);
            } else {
                this.handleStatusCodesFallback(getGenericMessage(statusCode, error));
            }
        },

        /**
         * An object of status code error handlers. If custom handler is defined by extending
         * module, corresponding status code handler will attemp to use that, otherwise,
         * handleStatusCodesFallback is used as a fallback just logging the error.
         * @property {Object}
         */
        statusCodes: {
            
            // oauth2 uses 400 as a sort of catch all; see:
            // http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-5.2
            400: function(xhr, error) {

                /**
                 * invalid_grant
                 *
                 * The provided authorization grant is invalid, expired, revoked, does 
                 * not match the redirection URI used in the authorization request, or
                 * was issued to another client.
                 * 
                 * This happens when logging in with improper user/pass.
                 *
                 * Provide a custom handleInvalidGrantError to override this. 
                 */
                this.callCustomIfMatchingError(xhr, error, ".*invalid_grant.*", '400', this.handleInvalidGrantError);
            
                /**
                 * invalid_client
                 *
                 * Client authentication failed (e.g. unknown client, no client 
                 * authentication included, multiple client authentications included, 
                 * or unsupported authentication method). 
                 *
                 * Provide a custom handleInvalidClientError to override this. 
                 */

                this.callCustomIfMatchingError(xhr, error, ".*invalid_client.*", '400', this.handleInvalidClientError);
            
                /**
                 * invalid_request
                 *
                 * The request is missing a required parameter, includes an unsupported 
                 * parameter or parameter value, repeats a parameter, includes multiple
                 * credentials, utilizes more than one mechanism for authenticating the 
                 * client, or is otherwise malformed.
                 *
                 * Provide a custom handleInvalidRequestError to override this.
                 */
                this.callCustomIfMatchingError(xhr, error, ".*invalid_request.*", '400', this.handleInvalidRequestError);
                
                // The authenticated client is not authorized to use this authorization grant type.
                this.callCustomIfMatchingError(xhr, error, ".*unauthorized_client.*", '400', this.handleUnauthorizedClientError);

                // The authorization grant type is not supported by the authorization server.
                this.callCustomIfMatchingError(xhr, error, ".*unsupported_grant_type.*", '400', this.handleUnsupportedGrantTypeError);
                
                // The requested scope is invalid, unknown, malformed, or exceeds the scope granted by the resource owner.
                this.callCustomIfMatchingError(xhr, error, ".*invalid_scope.*", '400', this.handleInvalidScopeError);
            },
            /**
             * Clients can provide a handleUnauthorizedError to override this.
             */
            401: function(xhr, error) {
                if(xhr && xhr.responseText) {
                    this.callCustomHandler(xhr, error, this.handleUnauthorizedError);
                }  else {
                    this.handleStatusCodesFallback(getGenericMessage('401', error));
                }
            },
            /**
             * Clients can provide a handleForbiddenError to override this.
             */
            403: function(xhr, error) {
                this.callCustomHandlerIfXhr(xhr, error, '403', this.handleForbiddenError);
            },
            404: function(xhr, error) {
                this.callCustomHandlerIfXhr(xhr, error, '404', this.handleNotFoundError);
            },
            /**
             * Clients can provide a handleMethodNotAllowedError to override this.
             */
            405: function(xhr, error) {
                this.callCustomHandlerIfXhr(xhr, error, '405', this.handleMethodNotAllowedError);
            },
            422: function(xhr, error, model) {
                this.handleValidationError(model, xhr.responseText);
            },
            500: function(xhr, error) {
                this.callCustomHandlerIfXhr(xhr, error, '500', this.handleServerError);
            }
        },

        remoteLogging: false,

        /**
         * Returns error strings given a error key and context
         * @param errorKey
         * @param context
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
         * @method
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
         * @method
         */
        handleHttpError: function(xhr, error, model) {
            // If we have a handler defined for this status code
            if(xhr) {
                if (xhr.status && this.statusCodes[xhr.status]) {
                    this.statusCodes[xhr.status].call(this, xhr, error, model);
                } else if(xhr.responseText) {
                    this.handleStatusCodesFallback(xhr.responseText);
                } else {
                    // TODO: Default catch all error code handler
                    // Temporarily going to the handleStatusCodesFallback handler but will probably need
                    // to go to a sensible "all other errors" type of handler.
                    this.handleStatusCodesFallback("Error in handleHttpError. No responseText available.");
                }
            }
        },

        /**
         * This is the default error handler we overload onerror with
         * @param {String} mesg Error message
         * @param {String} url URL of script
         * @param {String} line Line number of script
         * @method
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
         *     app.error = _.extend(app.error);
         *     // put your custom handlers here...
         * })(SUGAR.App);
         * </pre></code>
         * 
         * @param {String} mesg Error message
         * @method
         */
        handleStatusCodesFallback: function(mesg) {
            app.logger.error(mesg);
        },

        /**
         * Overloads the window.onerror catch all function. Calls the original if any while
         * adding the framework's custom error handling logic. Pass in a custom callback to
         * add additional error handling.
         * @param {Function} handler Callback function to call on error.
         * @param {Object} context Scope of the callback
         * @return {Boolean} False if onerror has already been overloaded.
         * @method
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
