(function(app) {
    var _rrh = {
        index: function(){
            var forecastModule = 'Forecasts';
            var forecastLayout = 'index';
            if(app.viewModule == 'config') {
                forecastLayout = app.viewModule;
            }
            app.controller.loadView({
                module: forecastModule,
                layout: forecastLayout
            });
        }
    }

    app.events.on("app:init", function(){
        app.logger.debug("Route changed to " + app.viewModule + " index!");
        app.router.route("", "index", _rrh.index);
    });
})(SUGAR.App);
