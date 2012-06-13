(function(app) {

    app.augment("config", {
        appId: 'nomad',
        env: 'dev',
        debugSugarApi: true,
        logLevel: 'DEBUG',
        logWriter: 'ConsoleWriter',
        logFormatter: 'SimpleFormatter',
        authStore: 'cache',
        serverUrl: '../../../sugarcrm/rest/v10',
        //serverUrl: 'http://localhost:8888/sugarcrm/rest/v10',
        clientID: "sugar",
        restVersion: '10',
        useHttps: false,
        alertAutoCloseDelay: 10000,
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
            'Accounts': {
                field: 'name',
                direction: 'asc'
            },
            'Cases': {
                field: 'case_number',
                direction: 'asc'
            }
        },
        syncConfig: false

    }, false);

})(SUGAR.App);