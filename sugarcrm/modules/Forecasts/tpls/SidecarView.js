(function(app) {
    app.events.on("app:init", function(){
        app.logger.debug("Route changed to Forecasts index!");
        app.router.route("", "index", function(){
            app.controller.loadView({
                module: 'Forecasts',
                layout: 'index'
            });
        });
        app.router.route("config", "config", function(){
            app.controller.loadView({
                module: "Forecasts",
                layout: "config"
            });
        });
    });

})(SUGAR.App);
