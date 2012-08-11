(function(app) {
    var _rrh = {
        index: function(){
            app.controller.loadView({
                module: app.viewModule,
                layout: app.viewModule.toLowerCase()
            });
        }
    }

    app.events.on("app:init", function(){
        app.logger.debug("Route changed to " + app.viewModule + " index!");
        app.router.route("", "index", _rrh.index);
    });
})(SUGAR.App);

