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
         * Logging level.
         * @cfg {Object} [logLevel=logger.Levels.DEBUG]
         */
        logLevel: app.logger.Levels.DEBUG,

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
        baseUrl: '/builds/ent/sugarcrm/rest'

    }, false);

})(SUGAR.App);
