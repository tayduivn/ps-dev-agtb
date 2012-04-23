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

})(SUGAR.App);