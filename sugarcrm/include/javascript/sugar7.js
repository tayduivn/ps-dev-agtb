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
                name: "bwc",
                route: "bwc/*url",
                callback: function(url) {
                    app.logger.debug("BWC: " + url);

                    var frame = $('#bwc-frame');
                    if (frame.length === 1 &&
                        'index.php' + frame.get(0).contentWindow.location.search === url
                        ) {
                        // update hash link only
                        return;
                    }

                    // if only index.php is given, redirect to Home
                    if (url === 'index.php') {
                        app.router.navigate('#Home', {trigger: true});
                        return;
                    }
                    var params = {
                        layout: 'bwc',
                        url: url
                    };
                    var module = /module=([^&]*)/.exec(url);
                    if (!_.isNull(module) && !_.isEmpty(module[1])) {
                        params.module = module[1];
                    }

                    app.controller.loadView(params);
                }
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
                        $el.tooltip('show');
                    } else {
                        $el.tooltip('destroy');
                    }
                }
        });

    });
})(SUGAR.App);
