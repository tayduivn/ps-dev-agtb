(function(app) {
    var module = {
        /**
         * Setups the params for error module
         * @param opts
         */
        initialize: function(opts) {
            console.log("initing");
            console.log(this);

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
             * @cfg {Boolean}
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
            },
            403: function() {
            },
            404: function() {
            },
            405: function() {
            },
            500: function() {
            }
        },

        remoteLogging: false,

        handleError: function(mesg, url, line) {
            app.logger.error(mesg + " at " + " on line " + line);
        },

        enableOnError: function(handler, context) {
            console.log("On Error", this.overloaded);
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
            }

            this.overloaded = true;
        }
    };

    app.augment("error", module);
    app.events.on("app:init", module.initialize, module);
})(SUGAR.App);