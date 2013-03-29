(function(app) {
    app.events.on("app:init", function() {
        Handlebars.registerHelper('modelRoute', function(model, action) {
            action = _.isString(action) ? action : null;
            var id = action == "create" ? "" : model.id,
                url,
                bwcActions = {
                    'create': 'EditView',
                    'edit': 'EditView',
                    'detail': 'DetailView'
                };

            var moduleMeta = app.metadata.getModule(model.module) || {};
            if (moduleMeta.isBwcEnabled) {
                url = app.bwc.buildRoute(model.module, id, bwcActions[action]);
            } else {
                //Normal Sidecar route
                url = app.router.buildRoute(model.module, id, action);
            }
            moduleMeta = null;
            return new Handlebars.SafeString(url);
        });

        Handlebars.registerHelper('moduleIconLabel', function(module) {
            return app.lang.getAppListStrings('moduleListSingular')[module].substring(0, 2);
        });
    });
})(SUGAR.App);
