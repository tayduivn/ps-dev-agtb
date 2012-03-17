/**
 * Application configuration.
 * @class Config
 * @alias SUGAR.App.config
 * @singleton
 */
(function(app) {

    app.augment("config", {
        /**
         * Application environment. Possible values: 'dev', 'test', 'prod'
         * @cfg {String}
         */
        env: 'dev',

        /**
         * Flag indicating whether to output Sugar API debug information.
         * @cfg {Boolean}
         */
        debugSugarApi: true,

        /**
         * Logging level.
         * @cfg {Object} [logLevel=logger.Levels.DEBUG]
         */
        logLevel: app.logger.levels.DEBUG,

        /**
         * Logging writer.
         * @cfg [logWrtiter=logger.ConsoleWriter]
         */
        logWriter: app.logger.ConsoleWriter,

        /**
         * Logging formatter.
         * @cfg [logFormatter=logger.SimpleFormatter]
         */
        logFormatter: app.logger.SimpleFormatter,

        /**
         * Sugar REST server URL.
         * @cfg {String}
         */
        baseUrl: '../sugarcrm/rest',

        /**
         * Max Query Result Set size
         */
        maxQueryResult: 20

    }, false);

})(SUGAR.App);
