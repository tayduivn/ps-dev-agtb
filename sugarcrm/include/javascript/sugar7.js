(function(app) {
    app.events.on("app:init", function() {
        var routes;

        function recordHandler(module, id, action) {
            var opts = {
                module: module,
                layout: "record",
                action: (action || "detail")
            };

            if (id !== "create") {
                _.extend(opts, {modelId: id});
            } else {
                _.extend(opts, {create: true});
                opts.layout = "create";
            }

            app.controller.loadView(opts);
        }

        routes = [
            {
                name: "index",
                route: "",
                callback: "index"
            },
            {
                name: "logout",
                route: "logout",
                callback: "logout"
            },
            {
                name: "logout",
                route: "logout/?clear=:clear",
                callback: "logout"
            },
            {
                name: "list",
                route: ":module",
                callback: function(module) {
                    console.log("listing");
                    app.controller.loadView({
                        module: module,
                        layout: "records"
                    });
                }
            },
            {
                name: "layout",
                route: ":module/layout/:view",
                callback: "layout"
            },
            {
                name: "create",
                route: ":module/create",
                callback: "create"
            },
            {
                name: "record action",
                route: ":module/:id/:action",
                callback: recordHandler
            },
            {
                name: "record",
                route: ":module/:id",
                callback: recordHandler
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