(function(app) {
    app.events.on("app:init", function() {
        var routes;

        routes = [
            {
                name: "index",
                route: ""
            },
            {
                name: "logout",
                route: "logout/?clear=:clear"
            },
            {
                name: "logout",
                route: "logout"
            },
            {
                name: "list",
                route: ":module"
            },
            {
                name: "record",
                route: ":module/create"
            },
            {
                name: "layout",
                route: ":module/layout/:view"
            },
            {
                name: "record",
                route: ":module/:id"
            },
            {
                name: "record",
                route: ":module/:id/:action"
            },
            {
                name:'config',
                route: ':module/config',
                callback: function(module) {
                    app.controller.loadView({
                        module: module,
                        layout: 'config'
                    });
                }
            }
        ];

        app.routing.setRoutes(routes);
    });
})(SUGAR.App);
