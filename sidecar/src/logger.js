(function(app) {

    app.augment("logger", {

        Levels: {
            TRACE: {
                value: 1,
                name:  "TRACE"
            },
            DEBUG: {
                value: 2,
                name:  "DEBUG"
            },
            INFO:  {
                value: 3,
                name:  "INFO"
            },
            WARN:  {
                value: 4,
                name:  "WARN"
            },
            ERROR: {
                value: 5,
                name:  "ERROR"
            },
            FATAL: {
                value: 6,
                name:  "FATAL"
            }
        },

        ConsoleWriter: {
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

        SimpleFormatter: {
            format: function(level, message, date) {
                var dateString = date.getUTCFullYear() + "-" + date.getUTCMonth() + "-" + date.getUTCDate() +
                    " " + date.getUTCHours() + ":" + date.getUTCMinutes() + ":" + date.getUTCSeconds();
                return level.name + "[" + dateString + "]: " + message;
            }
        },

        trace: function(message) {
            this.log(this.Levels.TRACE, message);
        },

        debug: function(message) {
            this.log(this.Levels.DEBUG, message);
        },

        info: function(message) {
            this.log(this.Levels.INFO, message);
        },

        warn: function(message) {
            this.log(this.Levels.WARN, message);
        },

        error: function(message) {
            this.log(this.Levels.ERROR, message);
        },

        fatal: function(message) {
            this.log(this.Levels.FATAL, message);
        },

        // TODO: We may want to add support for format strings like "Some message %s %d", params
        log:   function(level, message) {
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
