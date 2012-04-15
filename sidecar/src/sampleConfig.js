/**
 * Application configuration.
 * @class Config
 * @alias SUGAR.App.config
 * @singleton
 */
(function(app) {

    app.augment("config", {
        /**
         * Application identifier.
         * @cfg {String}
         */
        appId: 'portal',

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
         * @cfg {Object} [logLevel=Utils.Logger.Levels.DEBUG]
         */
        logLevel: app.logger.levels.DEBUG,

        /**
         * Logging writer.
         * @cfg [logWrtiter=Utils.Logger.ConsoleWriter]
         */
        logWriter: app.logger.ConsoleWriter,

        /**
         * Logging formatter.
         * @cfg [logFormatter=Utils.Logger.SimpleFormatter]
         */
        logFormatter: app.logger.SimpleFormatter,

        /**
         * Sugar REST server URL.
         *
         * The URL can relative or absolute.
         * @cfg {String}
         */
        serverUrl: '../../sugarcrm/rest/v10',

        /**
         * Max query result set size.
         * @cfg {Number}
         */
        maxQueryResult: 20,

        /**
         * Platform name.
         * @cfg {String}
         */
        platform: "portal"

    }, false);

})(SUGAR.App);