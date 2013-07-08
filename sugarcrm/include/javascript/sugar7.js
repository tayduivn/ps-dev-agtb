(function(app) {
    app.events.on("app:init", function() {
        var routes,
            homeOptions = {
                dashboard: 'dashboard',
                activities: 'activities'
            },
            getLastHomeKey = function() {
                return app.user.lastState.buildKey('last-home', 'app-header');
            };

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
                name: "home",
                route: "Home",
                callback: function() {
                    var lastHomeKey = getLastHomeKey(),
                        lastHome = app.user.lastState.get(lastHomeKey);

                    if (lastHome === homeOptions.dashboard) {
                        app.router.list("Home");
                    } else if (lastHome === homeOptions.activities) {
                        app.router.navigate('#activities', {trigger: true});
                    }
                }
            },
            {
                name: "activities",
                route: "activities",
                callback: function(){
                    //when visiting activity stream, save last state of activities
                    //so future Home routes go back to activities
                    var lastHomeKey = getLastHomeKey();
                    app.user.lastState.set(lastHomeKey, homeOptions.activities);

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
                name: 'config',
                route: ':module/config',
                callback: function(module) {
                    app.controller.loadView({
                        module: module,
                        layout: 'config'
                    });
                }
            },
            {
                name: "homeRecord",
                route: "Home/:id",
                callback: function(id) {
                    //when visiting a dashboard, save last state of dashboard
                    //so future Home routes go back to dashboard
                    var lastHomeKey = getLastHomeKey();
                    app.user.lastState.set(lastHomeKey, homeOptions.dashboard);

                    //then continue on with default record routing
                    app.router.record("Home", id);
                }
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
    //template language string for each page
    //i.e. records for listview, record for recordview
    var titles = {
            'records' : 'TPL_BROWSER_SUGAR7_RECORDS_TITLE',
            'record' : 'TPL_BROWSER_SUGAR7_RECORD_TITLE'
        },
        getTitle = function(model) {
            var context = app.controller.context,
                module = context.get("module"),
                template = Handlebars.compile(app.lang.get(titles[context.get("layout")], module) || ''),
                moduleString = app.lang.getAppListStrings('moduleList');

            //pass current translated module name and current page's model data
            return template(_.extend({
                module: moduleString[module],
                appId: app.config.appId
            }, model ? model.attributes : {}));
        },
        //set current document title with template format
        setTitle = function(model) {
            var title = getTitle(model);
            document.title = title || document.title;
        };
    //store previous view's model
    var prevModel;

    app.events.on("app:view:change", function() {
        var context = app.controller.context,
            module = context.get("module"),
            metadata = app.metadata.getModule(module),
            title;

        if(prevModel) {
            //if previous model is existed, clean out setTitle listener
            prevModel.off("change", setTitle);
        }

        if(_.isEmpty(metadata) || metadata.isBwcEnabled) {
            //For BWC module, current document title will be replaced with BWC title
            title = $('#bwc-frame').get(0) ? $('#bwc-frame').get(0).contentWindow.document.title : getTitle();
        } else {
            title = getTitle();
            if(!_.isEmpty(context.get("model"))) {
                //for record view, the title should be updated once model is fetched
                var currModel = context.get("model");
                currModel.on("change", setTitle, this);
                app.controller.layout.once("dispose", function(){
                    currModel.off("change", setTitle);
                });
                prevModel = currModel;
            }
        }
        document.title = title || document.title;
    }, this);

})(SUGAR.App);
