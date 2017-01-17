(function (app) {

    if (!app) {
        return false;
    }

    /**
     * Override SUGAR.App.config values when metadata fetch finished
     */
    app.events.on('app:sync:complete', function() {
        if (seedbed.sugarOverrideConfig && seedbed.utils.deepExtend) {
            app.config = seedbed.utils.deepExtend(app.config, seedbed.sugarOverrideConfig);
        }
    });

})(SUGAR && SUGAR.App ? SUGAR.App : null);
