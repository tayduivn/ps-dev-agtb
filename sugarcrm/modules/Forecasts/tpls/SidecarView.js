(function(app) {
    app.events.on("app:init", function(){
        app.logger.debug("Route changed to " + app.viewModule + " index!");
        app.router.route("", "index", function(){
            app.controller.loadView({
                module: 'Forecasts',
                layout: 'forecasts'
            });
        });
        app.router.route("config", "config", function(){
            app.controller.loadView({
                module: "Forecasts",
                layout: "forecastsEmpty"
            });
        });
    });

})(SUGAR.App);
