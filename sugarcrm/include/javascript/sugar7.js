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
                name: "activities",
                route: "activities",
                callback: function(){
                    app.controller.loadView({
                        layout: "activities",
                        module: "Activities",
                        skipFetch: true
                    });
                }
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
                        // on BWC import we want to try and take the import module as the module
                        if (module[1] === 'Import') {
                            module = /import_module=([^&]*)/.exec(url);
                            if (!_.isNull(module) && !_.isEmpty(module[1])) {
                                params.module = module[1];
                            }
                        }
                    }

                    app.controller.loadView(params);
                }
            },
            {
                name: "list",
                route: ":module"
            },
            {
                name: "create",
                route: ":module/create",
                callback: function(module){
                    if(module === "Home") {
                        app.controller.loadView({
                            module: module,
                            layout: "record"
                        });

                        return;
                    }

                    var previousModule = app.controller.context.get("module"),
                        previousLayout = app.controller.context.get("layout");
                    if(!(previousModule === module && previousLayout === "records")) {
                        app.controller.loadView({
                            module: module,
                            layout: "records"
                        });
                    }

                    app.drawer.open({
                        layout:'create-actions',
                        context:{
                            create:true
                        }
                    }, _.bind(function (context, model) {
                        var module = context.get("module") || model.module,
                            route  = app.router.buildRoute(module);

                        app.router.navigate(route, {trigger: (model instanceof Backbone.Model)});
                    }, this));
                }
            },
            {
                name: "vcardImport",
                route: ":module/vcard-import",
                callback: function(module){
                    app.controller.loadView({
                        module: module,
                        layout: "records"
                    });

                    app.drawer.open({
                        layout:'vcard-import'
                    }, _.bind(function () {
                        //if drawer is closed (cancel), just put the URL back to default view for module
                        var route = app.router.buildRoute(module);
                        app.router.navigate(route, {replace: true});
                    }, this));
                }
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
    });
})(SUGAR.App);
