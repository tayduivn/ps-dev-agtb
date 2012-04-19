(function(app) {
    app.augment("nomad", {

        deviceReady: function() {
            app.init({el: "#nomad" });
            app.logger.debug('App initialized');
            app.api.debug = app.config.debugSugarApi;
            app.start();
            app.logger.debug('App started');

            //document.addEventListener("backbutton", NOMAD.onBackButtonTouched, false); // for Android
        }

    });

    // Hack metadata module to ignore sugarFields and viewTemplates in the payload
    var _origSet = app.metadata.set;
    app.metadata.set = function(data) {
        data.sugarFields = null;
        data.viewTemplates = null;
        _origSet.call(this, data);
    };

})(SUGAR.App);