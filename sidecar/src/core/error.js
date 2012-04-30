(function(app) {
    /**
     * Error handling module.
     * @class Error
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

        /**
         * An object of status code error handlers.
         * @property {Object}
         */
        statusCodes: {
            400: function() {
            },
            401: function() {
                app.api.logout();
                app.router.login();
            },
            403: function() {
            },
            404: function() {
            },
            405: function() {
            },
            422: function(xhr, error, model) {
                this.handleValidationError(model, xhr.responseText);
            },
            500: function() {
            }
        },

        remoteLogging: false,

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
            _.each(errors, function(fieldError) {
                app.logger.error("validation failed: " + fieldError);
            });
        },

        /**
         * Handles http error codes returned from AJAX calls.
         * @param {XHR} xhr jQuery XHR Object
         * @param {String} error Error message
         * @method
         */
        handleHTTPError: function(xhr, error, model) {
            if (xhr.status && this.statusCodes[xhr.status]) {
                this.statusCodes[xhr.status].call(this, xhr, error, model);
            } else {
                // TODO: Default catch all error code handler
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
            app.logger.error(mesg + " at " + " on line " + line);
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

    app.augment("error", module);
    app.events.on("app:init", module.initialize, module);
})(SUGAR.App);