(function(test) {
    var app = SUGAR.App;
    test.loadComponent = function(client, type, name) {
        SugarTest.loadFile("../clients/" + client + "/" + type + "s/" + name + "/", name, "js", function(data) {
            app.view.declareComponent(type, name, null, data, null, true);
        });
    };

    test.createField = function(client, name, type, viewName, fieldDef, module) {
        test.loadComponent(client, "field", type);
        var context = app.context.getContext();
        var view = new app.view.View({ name: viewName, context: context });
        var def = { name: name, type: type };

        var model = new Backbone.Model();

        if (fieldDef) {
            model.fields = {};
            model.fields[name] = fieldDef;
        }

        return app.view.createField({
            def: def,
            view: view,
            context: context,
            model: model
        });
    };

    test.createView = function(client, module, viewName, meta, model) {
        test.loadComponent(client, "view", viewName);
        var context = app.context.getContext({'model': model});
        return app.view.createView({
            name : viewName,
            context : context,
            module : module,
            meta : meta
        });
    };

    test.createLayout = function(client, module, layoutName, meta) {
        test.loadComponent(client, "layout", layoutName);
        var context = app.context.getContext();
        return app.view.createLayout({
            name : layoutName,
            context : context,
            module : module,
            meta : meta
        });
    };

}(SugarTest));
