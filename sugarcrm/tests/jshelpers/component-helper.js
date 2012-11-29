(function(test) {
    var app = SUGAR.App;
    test.loadComponent = function(client, type, name, module) {
        var path = "/clients/" + client + "/" + type + "s/" + name;
        path = (module) ? "../modules/" + module + path : ".." + path;

        SugarTest.loadFile(path, name, "js", function(data) {
            try {
                data = eval("[" + data + "][0]");
            } catch (e) {
                app.logger.error("Failed to eval view controller for " + name + ": " + e + ":\n" + data);
            }
            app.view.declareComponent(type, name, module, data, null, true);
            test.testMetadata.addController(client, name, type, data, module);
        });
    };

    test.loadHandlebarsTemplate = function(name, type, client, module) {
        var path = "/clients/" + client + "/" + type + "s/" + name;
        path = (module) ? "../modules/" + module + path : ".." + path;
        SugarTest.loadFile(path, name, "hbt", function(data) {
            test.testMetadata.addTemplate(name, type, data, module);
        });
    };

    test.createField = function(client, name, type, viewName, fieldDef, module) {
        test.loadComponent(client, "field", type, module);
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

    test.createView = function(client, module, viewName, meta, context, loadFromModule) {
        if (loadFromModule) {
            test.loadComponent(client, "view", viewName, module);
        } else {
            test.loadComponent(client, "view", viewName);
        }
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

    test.createLayout = function(client, module, layoutName, meta, context, loadFromModule) {
        if (loadFromModule) {
            test.loadComponent(client, "layout", layoutName, module);
        } else {
            test.loadComponent(client, "layout", layoutName);
        }
        if (!context) {
            context = app.context.getContext();
            context.set({
                module: module,
                layout: layoutName
            });
            context.prepare();
        }

        return app.view.createLayout({
            name : layoutName,
            context : context,
            module : module,
            meta : meta
        });
    };

    test.testMetadata = {
        _data: null,

        init: function() {
            this._data = $.extend(true, {}, fixtures.metadata);
            this._data.layouts = this._data.layouts || {};
            this._data.views = this._data.views || {};
            this._data.fields = this._data.fields || {};
        },

        addController: function(client, name, type, controller, module) {
            type = type + 's';
            if (this.isInitialized()) {
                if (module) {
                    this._initModuleStructure(module, type, name);
                    this._data.modules[module][type][name].controller = controller;
                } else {
                    this._data[type][client][name] = this._data[type][client][name] || {};
                    this._data[type][client][name].controller = controller;
                }
            }
        },

        addTemplate: function(name, type, template, module) {
            type = type + 's';
            if (this.isInitialized()) {
                if (module) {
                    this._initModuleStructure(module, type, name);
                    this._data.modules[module][type][name].template = template;
                } else {
                    this._data[type][name] = this._data[type][name] || {};
                    this._data[type][name].templates = this._data[type][name].templates || {};
                    this._data[type][name].templates[name] = template;
                }
            }
        },

        addViewDefinition: function(name, viewDef) {
            if (this.isInitialized()) {
                this._data.views[name] = this._data.views[name] || {};
                this._data.views[name].meta = viewDef;
            }
        },

        addModuleViewDefinition: function(module, name, viewDef) {
            if (this.isInitialized()) {
                this._data.modules[module].views = this._data.modules[module].views || {};
                this._data.modules[module].views[name] = this._data.modules[module].views[name] || {};
                this._data.modules[module].views[name].meta = viewDef;
            }
        },

        addModuleDefinition: function(module, moduleDef) {
            if (this.isInitialized()) {
                this._data.modules[module] = moduleDef;
            }
        },

        _initModuleStructure: function(module, type, name) {
            this._data.modules[module] = this._data.modules[module] || {};
            this._data.modules[module][type] = this._data.modules[module][type] || {};
            this._data.modules[module][type][name] = this._data.modules[module][type][name] || {};
        },

        set: function() {
            if (this.isInitialized()) {
                _.each(this._data.modules, function(module) {
                    module._patched = false;
                });
                SugarTest.app.metadata.set(this._data, true, true);
            }
        },

        revert: function() {
            if (this.isInitialized()) {
                SugarTest.app.metadata.set(fixtures.metadata, true, true);
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