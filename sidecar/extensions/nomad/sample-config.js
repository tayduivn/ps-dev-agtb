(function(app) {

    app.augment("config", {
        appId: 'nomad',
        env: 'dev',
        debugSugarApi: true,
        logLevel: app.logger.levels.DEBUG,
        logWriter: app.logger.ConsoleWriter,
        logFormatter: app.logger.SimpleFormatter,
        serverUrl: '../../../sugarcrm/rest/v10',
        restVersion: '10',
        maxQueryResult: 20,
        platform: "mobile",
        defaultModule: "Accounts",
        metadataTypes: ["acl", "appListStrings", "appStrings", "modStrings", "moduleList", "modules"],
        additionalComponents: {
            "header": {
                target: '#header'
            },
            alert: {
                target: '#alert'
            }
        },
        orderByDefaults: {
            'Cases': {
                field: 'case_number',
                direction: 'asc'
            }
        }

    }, false);

})(SUGAR.App);