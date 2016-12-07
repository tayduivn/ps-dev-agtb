(function(app) {

    app.augment('config', {

        appId: 'portal',
        env: 'dev',
        debugSugarApi: true,
        logger: {
            level: 'DEBUG'
        },
        logWriter: 'ConsoleWriter',
        logFormatter: 'SimpleFormatter',

        // For tests our context is the runner which is one level lower!
        serverUrl: '../../../rest/v10',
        siteUrl: '../../..',

        serverTimeout: 30,
        maxQueryResult: 20,

        maxSearchQueryResult: 3,
        unsecureRoutes: ['signup', 'error'],
        platform: 'base',
        defaultModule: 'Cases',
        metadataTypes: [],

        orderByDefaults: {
            'Cases': {
                field: 'case_number',
                direction: 'asc'
            },
            'Bugs': {
                field: 'bug_number',
                direction: 'asc'
            },
            'Notes': {
                field: 'date_modified',
                direction: 'desc'
            }
        },

        additionalComponents: {
            header: {
                target: '#header'
            },
            alert: {
                target: '#alert'
            },
            footer: {
                target: '#footer'
            }
        },

        displayModules: [
            'Bugs',
            'Cases',
            'KBDocuments'
        ],

        clientID: 'sugar',
        syncConfig: false,
        loadCss: false,

    }, false);

})(SUGAR.App);
