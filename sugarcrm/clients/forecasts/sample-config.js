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
        appId: 'core-app',

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
        * # of fields to display on the detail view
        * @cfg {Number}
        */
        fieldsToDisplay: 5,

        /**
         * Platform name.
         * @cfg {String}
         */
        platform: "forecasts",

        /**
         * Default module to load for the home route (index).
         * If not specified, the framework loads `home` layout for the module `Home`.
         */
        defaultModule: "Opportunities",

        /**
         * A list of metadata types to fetch by default.
         * @cfg {Array}
         */
        metadataTypes: [],
        /**
         * Hash of addtional views of the format below to init and render on app start
         ** <pre><code>
         *         additionalComponents: {
         *            viewName: {
         *                target: 'CSSDomselector'
         *            }
         *        }
         * </pre></code>
         * @cfg {Array}
         */
        additionalComponents: {
            alert: {
                target: '#alert'
            }
        },
        
        /**
         * Client ID for oAuth
         * Defaults to sugar other values are support_portal
         * @cfg {Array}
         */
        clientID: "sugar"

        /**
          * Override the authentication storage
          */
        //,authStore:"sugarAuthStore"


    }, false);

})(SUGAR.App);
