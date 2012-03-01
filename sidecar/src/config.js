/**
 * Application configuration.
 * @class config
 * @alias SUGAR.App.config
 * @singleton
 */
(function(app) {

    app.augment("config", {
        /**
         * Application environment. Possible values: 'dev', 'test', 'prod'
         * @type {String}
         */
        env: 'dev',

        /**
         * Logging level.
         * @property {logger.Levels} [logLevel=logger.Levels.DEBUG]
         */
        logLevel: app.logger.Levels.DEBUG,

        /**
         * Logging writer.
         * @property [logWrtiter=logger.ConsoleWriter]
         */
        logWriter: app.logger.ConsoleWriter,

        /**
         * Logging formatter.
         * @property [logFormatter=logger.SimpleFormatter]
         */
        logFormatter: app.logger.SimpleFormatter,

        /**
         * Sugar REST server URL.
         */
        baseUrl: '/mango_build/Pineapple/ent/sugarcrm/rest'

    }, false);

})(SUGAR.App);
