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
            }
        ];

        app.routing.setRoutes(routes);
        app.utils = _.extend(app.utils, {
                handleTooltip: function(event, viewComponent) {
                    var $el = viewComponent.$(event.target);
                    if( $el[0].offsetWidth < $el[0].scrollWidth ) {
                        $el.tooltip({placement:"top", container: "body"});
                        $el.tooltip('show');
                    } else {
                        $el.tooltip('destroy');
                    }
                }
        });

    });
})(SUGAR.App);
