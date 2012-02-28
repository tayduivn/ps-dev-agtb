/**
 * Application logger.
 * <pre>
 *     // Log a string message
 *     SUGAR.App.logger.debug("Some debug message");
 *
 *     // Log an object
 *     var obj = { foo: "bar" };
 *     SUGAR.App.logger.info(obj);
 *
 *     // Log a closure.
 *     var a = 1;
 *     SUGAR.App.logger.error(function() { return a; });
 * </pre>
 * @class logger.
 * @alias SUGAR.App.logger
 * @singleton
 */
(function(app) {

    app.augment("logger", {

        /**
         * @class logger.Levels
         * Logging levels.
         * @singleton
         */
        Levels: {
            /**
             * @class logger.Levels.TRACE
             * @singleton
             */
            TRACE: {
                value: 1,
                name: "TRACE"
            },
            /**
             * @class logger.Levels.DEBUG
             * @singleton
             */
            DEBUG: {
                value: 2,
                name: "DEBUG"
            },
            /**
             * @class logger.Levels.INFO
             * @singleton
             */
            INFO: {
                value: 3,
                name: "INFO"
            },
            /**
             * @class logger.Levels.WARN
             * @singleton
             */
            WARN: {
                value: 4,
                name: "WARN"
            },
            /**
             * @class logger.Levels.ERROR
             * @singleton
             */
            ERROR: {
                value: 5,
                name: "ERROR"
            },
            /**
             * @class logger.Levels.FATAL
             * @singleton
             */
            FATAL: {
                value: 6,
                name: "FATAL"
            }
        },

        /**
         * @class logger.ConsoleWriter
         * Outputs messages onto browser's console object.
         * @singleton
         */
        ConsoleWriter: {
            /**
             * Outputs a message with the specified log level onto the browser's console.
             * The writer uses
             * <code>console.info</code> for <code>TRACE</code>, <code>DEBUG</code> and <code>INFO<code>,
             * <code>console.warn</code> for <code>WARN</code>, and
             * <code>console.error</code> for <code>ERROR</code> and <code>FATAL</code>.
             * @param {logger.Levels} level
             * @param {String} message
             */
            write: function(level, message) {
                if (level.value <= app.logger.Levels.INFO.value) {
                    console.info(message);
                }
                else if (level.value == app.logger.Levels.WARN.value) {
                    console.warn(message);
                }
                else {
                    console.error(message);
                }
            }
        },

        /**
         * @class logger.SimpleFormatter
         * Formats a log message as a string with log level and UTC timestamp.
         * <pre>
         * // Log a trace message
         * SUGAR.App.logger.trace("Blah-blah");
         *
         * // Output
         * // TRACE[2012-1-26 2:38:23]: Blah-blah
         * </pre>
         * @singleton
         */
        SimpleFormatter: {
            /**
             * Formats a log message by adding log level name and UTC timestamp.
             * @param {logger.Levels} level logging level
             * @param {String} message log message
             * @param {Date} date logging timestamp
             */
            format: function(level, message, date) {
                var dateString = date.getUTCFullYear() + "-" + date.getUTCMonth() + "-" + date.getUTCDate() +
                    " " + date.getUTCHours() + ":" + date.getUTCMinutes() + ":" + date.getUTCSeconds();
                return level.name + "[" + dateString + "]: " + message;
            }
        },

        /**
         * Logs a message with {@link logger.Levels.TRACE} log level.
         * @param {String/Object/Function} message log message
         * @member logger
         */
        trace: function(message) {
            this.log(this.Levels.TRACE, message);
        },

        /**
         * Logs a message with {@link logger.Levels.DEBUG} log level.
         * @param {String/Object/Function} message log message
         * @member logger
         */
        debug: function(message) {
            this.log(this.Levels.DEBUG, message);
        },

        /**
         * Logs a message with {@link logger.Levels.INFO} log level.
         * @param {String/Object/Function} message log message
         * @member logger
         */
        info: function(message) {
            this.log(this.Levels.INFO, message);
        },

        /**
         * Logs a message with {@link logger.Levels.WARN} log level.
         * @param {String/Object/Function} message log message
         * @member logger
         */
        warn: function(message) {
            this.log(this.Levels.WARN, message);
        },

        /**
         * Logs a message with {@link logger.Levels.ERROR} log level.
         * @param {String/Object/Function} message log message
         * @member logger
         */
        error: function(message) {
            this.log(this.Levels.ERROR, message);
        },

        /**
         * Logs a message with {@link logger.Levels.FATAL} log level.
         * @param {String/Object/Function} message log message
         * @member logger
         */
        fatal: function(message) {
            this.log(this.Levels.FATAL, message);
        },

        // TODO: We may want to add support for format strings like "Some message %s %d", params
        /**
         * Logs a message with a given {@link logger.Levels} level.
         * If the message is an object, it will be serialized into a JSON string.
         * If the message is a function, it will eveluated in the logger's scope.
         * @param {logger.Levels} level log level
         * @param {String/Object/Function} message log message
         * @member logger
         */
        log: function(level, message) {
            try {
                message = message || "<none>";
                var l = app.config.logLevel || this.Levels.ERROR;
                var writer = app.config.logWriter || this.ConsoleWriter;
                var formatter = app.config.logFormatter || this.SimpleFormatter;

                if (level.value >= l.value) {
                    if (_.isFunction(message)) message = message.call(this);
                    if (_.isObject(message)) message = JSON.stringify(message);
                    writer.write(level, formatter.format(level, message, new Date));
                }
            }
            catch (e) {
                console.log("Failed to log message {" + message + "} due to exception: " + e);
            }
        }
    }, false);

})(SUGAR.App);
