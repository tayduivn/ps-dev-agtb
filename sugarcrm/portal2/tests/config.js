/**
 * Application configuration.
 * @class Config
 * @alias SUGAR.App.config
 * @singleton
 */
(function(app) {

    app.augment('config', {

        appId: 'portal',
        platform: 'portal',
        clientID: 'sugar',
        syncConfig: false,

    }, false);

})(SUGAR.App);
