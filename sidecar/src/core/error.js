(function(app) {
    var module = {
        /**
         * Setups the params for error module
         * @param opts
         */
        initialize: function(opts) {
            console.log("initing");
            console.log(this);
            // Enable remote error logging
            this.remoteLogging = opts.remoteLogging || false;

            // Inject status code handler
            this.statusCodes = opts.statusCodes || this.statusCodes;

            if (!opts.disableOnError) {
                this.onError();
            }
        },

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

        handleError: function(e) {

        },

        onError: function(handler, context) {
            console.log("On Error");
            var originalHandler;

            if (!window.onerror) {
                return false;
            }

            originalHandler = window.onerror;

            window.onerror = function(mesg, url, line) {
                if (handler) {
                    handler.call(context);
                } else {
                    alert(arguments);
                }

                if (originalHandler) {
                    originalHandler();
                }
            }
        }
    };

    app.augment("error", module);
    app.events.on("app:init", module.initialize, module);
})(SUGAR.App);