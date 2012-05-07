(function(app) {

    app.augment("config", {
        appId: 'nomad',
        env: 'dev',
        debugSugarApi: true,
        logLevel: app.logger.levels.DEBUG,
        logWriter: app.logger.ConsoleWriter,
        logFormatter: app.logger.SimpleFormatter,
        serverUrl: 'http://localhost:8888/sugarcrm/rest/v10',
        maxQueryResult: 20,
        platform: "mobile",
        metadataTypes: ["acl", "appListStrings", "appStrings", "modStrings", "moduleList", "modules"],
        additionalComponents: {
            "header": {
                target: '#header'
            }
        }

    }, false);

})(SUGAR.App);