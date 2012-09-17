(function(test) {
    var app = SUGAR.App;
    test.loadComponent = function(client, type, name) {
        SugarTest.loadFile("../clients/" + client + "/" + type + "s/" + name + "/", name, "js", function(data) {
            app.view.declareComponent(type, name, null, data, null, true);
        });
    };

    test.loadModuleComponent = function(module, client, type, name) {
        SugarTest.loadFile("../modules/" + module + "/clients/" + client + "/" + type + "s/" + name + "/", name, "js", function(data) {
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

    test.createView = function(client, module, viewName, meta, context) {
        context = context || app.context.getContext();
        test.loadComponent(client, "view", viewName);
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

    test.createModuleLayout = function(client, module, layoutName, meta) {
        test.loadModuleComponent(module, client, "layout", layoutName);
        var context = app.context.getContext(),
            params = {
                module: module,
                modelId: 'eab15fea-a4b5-c63d-8365-50353a164161',
                layout: layoutName
            };

        context.set(params);
        context.prepare();

        return app.view.createLayout({
            name : layoutName,
            context : context,
            module : module,
            meta : meta
        });
    };

    test.createModuleLayout = function(client, module, layoutName, meta) {
        test.loadModuleComponent(module, client, "layout", layoutName);
        var context = app.context.getContext(),
            params = {
                module: module,
                modelId: 'eab15fea-a4b5-c63d-8365-50353a164161',
                layout: layoutName
            };

        context.set(params);
        context.prepare();

        return app.view.createLayout({
            name : layoutName,
            context : context,
            module : module,
            meta : meta
        });
    };

    test.createModuleView = function(client, module, viewName, meta) {
        test.loadModuleComponent(module, client, "view", viewName);
        var context = app.context.getContext(),
            params = {
                module: module,
                modelId: 'eab15fea-a4b5-c63d-8365-50353a164161',
                layout: viewName
            };

        context.set(params);
        context.prepare();

        return app.view.createView({
            name : viewName,
            context : context,
            module : module,
            meta : meta
        });
    };

}(SugarTest));
