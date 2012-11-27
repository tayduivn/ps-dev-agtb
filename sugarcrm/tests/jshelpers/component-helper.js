(function(test) {
    var app = SUGAR.App;
    test.loadComponent = function(client, type, name) {
        SugarTest.loadFile("../clients/" + client + "/" + type + "s/" + name, name, "js", function(data) {
            try {
                data = eval("[" + data + "][0]");
            } catch (e) {
                app.logger.error("Failed to eval view controller for " + name + ": " + e + ":\n" + data);
            }
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
            try {
                data = eval("[" + data + "][0]");
            } catch (e) {
                app.logger.error("Failed to eval view controller for " + name + ": " + e + ":\n" + data);
            }
            app.view.declareComponent(type, name, module, data, null, true);
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
        test.loadComponent(client, "view", viewName);
        if (!context) {
            context = app.context.getContext();
            context.set({
                module: module
            });
            context.prepare();
        }

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
                this._data.views.base = this._data.views.base || {};
                this._data.views.base[name] = this._data.views.base[name] || {};
                this._data.views.base[name].templates = this._data.views.base[name].templates || {};
                this._data.views.base[name].templates[name] = template;
            }
        },

        addViewController: function(name, controller) {
            if (this.isInitialized()) {
                this._data.views.base = this._data.views.base || {};
                this._data.views.base[name] = this._data.views.base[name] || {};
                this._data.views.base[name].controller = controller;
            }
        },

        addViewDefinition: function(name, viewDef) {
            if (this.isInitialized()) {
                this._data.views = this._data.views || {};
                this._data.views.base = this._data.views.base || {};
                this._data.views.base[name] = this._data.views.base[name] || {};
                this._data.views.base[name].meta = viewDef;
            }
        },

        addModuleViewDefinition: function(module, name, viewDef) {
            if (this.isInitialized()) {
                this._data.modules[module].views = this._data.modules[module].views || {};
                this._data.modules[module].views[name] = this._data.modules[module].views[name] || {};
                this._data.modules[module].views[name].meta = viewDef;
            }
        },

        addFieldTemplate: function(type, name, template) {
            if (this.isInitialized()) {
                this._data.fields.base =this._data.fields.base || {};
                this._data.fields.base[type] = this._data.fields.base[type] || {};
                this._data.fields.base[type].templates = this._data.fields.base[type].templates || {};
                this._data.fields.base[type].templates[name] = template;
            }
        },

        addFieldController: function(name, controller) {
            if (this.isInitialized()) {
                this._data.fields.base =this._data.fields.base || {};
                this._data.fields.base[name] = this._data.fields.base[name] || {};
                this._data.fields.base[name].controller = controller;
            }
        },

        addModuleDefinition: function(module, moduleDef) {
            if (this.isInitialized()) {
                this._data.modules[module] = moduleDef;
            }
        },

        set: function() {
            if (this.isInitialized()) {
                _.each(this._data.modules, function(module) {
                    module._patched = false;
                });
                SugarTest.app.metadata.set(this._data, false, true);
            }
        },

        revert: function() {
            if (this.isInitialized()) {
                SugarTest.app.metadata.set(fixtures.metadata, false, true);
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
        },

        get: function() {
            return this._data;
        }
    };
}(SugarTest));
