/**
 * Application logger.
 * <pre><code>
 * // Log a string message
 * SUGAR.App.logger.debug("Some debug message");
 *
 * // Log an object
 * var obj = { foo: "bar" };
 * SUGAR.App.logger.info(obj);
 *
 * // Log a closure.
 * var a = 1;
 * SUGAR.App.logger.error(function() { return a; });
 * </code></pre>
 * @class Logger
 * @alias SUGAR.App.logger
 * @singleton
 */
(function(app) {

    app.augment("logger", {

        /**
         * The different types of logging levels availabe
         * @class Logger.levels
         * @singleton
         */
        levels: {
            /**
             * Trace log level
             * @property {Logger.levels}
             */
            TRACE: {
                value: 1,
                name: "TRACE"
            },
            /**
             * Debug log level
             * @property {Logger.levels}
             */
            DEBUG: {
                value: 2,
                name: "DEBUG"
            },
            /**
             * Info log level
             * @property {Logger.levels}
             */
            INFO: {
                value: 3,
                name: "INFO"
            },
            /**
             * Warn log level
             * @property {Logger.levels}
             */
            WARN: {
                value: 4,
                name: "WARN"
            },
            /**
             * Error log level
             * @property {Logger.levels}
             */
            ERROR: {
                value: 5,
                name: "ERROR"
            },
            /**
             * Fatal log level
             * @property {Logger.levels}
             */
            FATAL: {
                value: 6,
                name: "FATAL"
            }
        },

        /**
         * Outputs messages onto browser's console object.
         * @class Logger.ConsoleWriter
         * @singleton
         * @member Logger
         */
        ConsoleWriter: {
            /**
             * Outputs a message with the specified log level onto the browser's console.
             * The writer uses
             * <code>console.info</code> for <code>TRACE</code>, <code>DEBUG</code> and <code>INFO<code>,
             * <code>console.warn</code> for <code>WARN</code>, and
             * <code>console.error</code> for <code>ERROR</code> and <code>FATAL</code>.
             * @param {Logger.levels} level A logger level from logger.levels
             * @param {String} message
             * @method
             */
            write: function(level, message) {
                if (level.value <= app.logger.levels.INFO.value) {
                    console.info(message);
                }
                else if (level.value == app.logger.levels.WARN.value) {
                    console.warn(message);
                }
                else {
                    console.error(message);
                }
            }
        },

        /**
         * Formats a log message as a string with log level and UTC timestamp.
         * <pre><code>
         * // Log a trace message
         * SUGAR.App.logger.trace("Blah-blah");
         *
         * // Output
         * // TRACE[2012-1-26 2:38:23]: Blah-blah
         * </code></pre>
         * @class Logger.SimpleFormatter
         * @member Logger
         * @singleton
         */
        SimpleFormatter: {
            /**
             * Formats a log message by adding log level name and UTC timestamp.
             * @param {Object} level logging level
             * @param {String} message log message
             * @param {Date} date logging timestamp
             * @method
             */
            format: function(level, message, date) {
                var dateString = date.getUTCFullYear() + "-" + date.getUTCMonth() + "-" + date.getUTCDate() +
                    " " + date.getUTCHours() + ":" + date.getUTCMinutes() + ":" + date.getUTCSeconds();
                return level.name + "[" + dateString + "]: " + message;
            }
        },

        /**
         * Logs a message with a TRACE log level.
         * @param {String/Object/Function} message log message
         * @member Logger
         */
        trace: function(message) {
            this.log(this.levels.TRACE, message);
        },

        /**
         * Logs a message with DEBUG log level.
         * @param {String/Object/Function} message log message
         * @member Logger
         */
        debug: function(message) {
            this.log(this.levels.DEBUG, message);
        },

        /**
         * Logs a message with INFO log level.
         * @param {String/Object/Function} message log message
         * @member Logger
         */
        info: function(message) {
            this.log(this.levels.INFO, message);
        },

        /**
         * Logs a message with WARN log level.
         * @param {String/Object/Function} message log message
         * @member Logger
         */
        warn: function(message) {
            this.log(this.levels.WARN, message);
        },

        /**
         * Logs a message with ERROR log level.
         * @param {String/Object/Function} message log message
         * @member Logger
         */
        error: function(message) {
            this.log(this.levels.ERROR, message);
        },

        /**
         * Logs a message with FATAL log level.
         * @param {String/Object/Function} message log message
         * @member Logger
         */
        fatal: function(message) {
            this.log(this.levels.FATAL, message);
        },

        // TODO: We may want to add support for format strings like "Some message %s %d", params
        /**
         * Logs a message with a given {@link Logger.levels} level.
         * If the message is an object, it will be serialized into a JSON string.
         * If the message is a function, it will eveluated in the logger's scope.
         * @param {Logger.levels} level log level
         * @param {String/Object/Function} message log message
         * @member Logger
         */
        log: function(level, message) {
            try {
                message = message || "<none>";
                var l = app.config.logLevel || this.levels.ERROR;
                var writer = app.config.logWriter || this.ConsoleWriter;
                var formatter = app.config.logFormatter || this.SimpleFormatter;

                if (level.value >= l.value) {
                    if (_.isFunction(message)) message = message.call(this);
                    if (_.isObject(message)) message = JSON.stringify(message);
                    writer.write(level, formatter.format(level, message, new Date()));
                }
            }
            catch (e) {
                console.log("Failed to log message {" + message + "} due to exception: " + e);
            }
        }
    }, false);

})(SUGAR.App);
