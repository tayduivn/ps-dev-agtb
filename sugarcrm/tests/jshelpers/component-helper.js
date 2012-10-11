(function(test) {
    var app = SUGAR.App;
    test.loadComponent = function(client, type, name) {
        SugarTest.loadFile("../clients/" + client + "/" + type + "s/" + name, name, "js", function(data) {
            app.view.declareComponent(type, name, null, data, null, true);
            if (type === 'view') {
                test.testMetadata.addViewController(name, data);
            } else if (type === 'field') {
                test.testMetadata.addFieldController(name, data);
            }
        });
    };

    test.loadModuleComponent = function(module, client, type, name) {
        SugarTest.loadFile("../modules/" + module + "/clients/" + client + "/" + type + "s/" + name, name, "js", function(data) {
            app.view.declareComponent(type, name, null, data, null, true);
            test.testMetadata.addViewController(name, data);
        });
    };

    test.loadViewHandlebarsTemplate = function(client, name) {
        SugarTest.loadFile("../clients/" + client + "/views/" + name, name, "hbt", function(data) {
            test.testMetadata.addViewTemplate(name, data);
        });
    };

    test.loadFieldHandlebarsTemplate = function(client, type, name) {
        SugarTest.loadFile("../clients/" + client + "/fields/" + type, name, "hbt", function(data) {
            test.testMetadata.addFieldTemplate(type, name, data);
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
        if (!context) {
            context = app.context.getContext();
            context.set({
                module: module
            });
            context.prepare();
        }

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
        var context = app.context.getContext(),
            params = {
                module: module,
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

    test.testMetadata = {
        _data: null,

        init: function() {
            this._data = $.extend(true, {}, fixtures.metadata);
        },

        addViewTemplate: function(name, template) {
            if (this.isInitialized()) {
                this._data.views[name] = this._data.views[name] || {};
                this._data.views[name].templates = this._data.views[name].templates || {};
                this._data.views[name].templates[name] = template;
            }
        },

        addViewController: function(name, controller) {
            if (this.isInitialized()) {
                this._data.views[name] = this._data.views[name] || {};
                this._data.views[name].controller = controller;
            }
        },

        addFieldTemplate: function(type, name, template) {
            if (this.isInitialized()) {
                this._data.fields[type] = this._data.fields[type] || {};
                this._data.fields[type].templates = this._data.fields[type].templates || {};
                this._data.fields[type].templates[name] = template;
            }
        },

        addFieldController: function(name, controller) {
            if (this.isInitialized()) {
                this._data.fields[name] = this._data.fields[name] || {};
                this._data.fields[name].controller = controller;
            }
        },

        apply: function() {
            if (this.isInitialized()) {
                SugarTest.app.metadata.set(this._data, false);
            }
        },

        revert: function() {
            if (this.isInitialized()) {
                SugarTest.app.metadata.set(fixtures.metadata, false);
            }
        },

        dispose: function() {
            this.revert();
            this._data = null;
        },

        isInitialized: function() {
            if (this._data) {
                return true;
            } else {
                return false;
            }
        }
    };
}(SugarTest));
