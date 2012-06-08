(function(app) {
    //Pull the precompile header and footer from the node precompile implementation for handlebars
    var _header = "(function() {\n  var template = Handlebars.template, templates = Handlebars.templates = Handlebars.templates || {};\n",
        _footer = '})();',
        _templates = {};

    /**
     * Manages Handlebars templates.
     * @class View.TemplateManager
     * @singleton
     * @alias SUGAR.App.template
     */
    var _templateManager = {

        /**
         * Loads templates from local storage and populates `Handlebars.templates` collection.
         */
        init: function() {
            _templates = app.cache.get("templates") || {};
            var src = "";
            _.each(_templates, function(t) {
                src += t;
            });

            try {
                eval(_header + src + _footer);
            }
            catch (e) {
                app.logger.error("Failed to eval templates retrieved from local storage:\n" + e);
                // TODO: Trigger app:error event
            }
        },

        /**
         * Conditionally compiles a template.
         * @param {Array} tpl First item is template key, the second is compiled template.
         * @param {String} src Template source code.
         * @param {Boolean} force Flag indicating if the template must be re-compiled.
         * @private
         * @ignore
         */
        _compile: function(tpl, src, force) {
            return (force || !tpl[1]) ?
                this.compile(tpl[0], src) :
                tpl[1];
        },

        /**
         * Compiles a template.
         *
         * This method puts the precompiled version of the template in cache and returns the compiled template.
         * The template can be accessed directly via `Handlebars.templates[key]` statement.
         *
         * @param {String} key Template identifier.
         * @param {String} src The actual template source to be compiled.
         * @return {Function} Compiled template.
         */
        compile: function(key, src) {
            try {
                _templates[key] = "templates['" + key + "'] = template(" + Handlebars.precompile(src) + ");\n";
                app.cache.set("templates", _templates);
                eval(_header + _templates[key] + _footer);
            } catch (e) {
                // Invalid templates will cause a JS error when they either pre-compile or compile.
                app.logger.error("Failed to compile or eval template " + key + ".\n" + e);
                // TODO: Trigger app:error event
            }

            return this.get(key);
        },

        /**
         * Retrieves a compiled handlebars template.
         * @param {String} key Identifier of the template to be retrieved.
         * @return {Function} Compiled Handlebars template.
         */
        get: function(key) {
            return Handlebars.templates ? Handlebars.templates[key] : null;
        },

        // Convenience private method
        _getView: function(name, module) {
            var key = name + (module ? ("." + module) : "");
            return [key, this.get(key)];
        },

        /**
         * Gets compiled template for a view.
         * @param {String} name View name.
         * @param {String} module(optional) Module name.
         * @return {Function} Compiled template.
         */
        getView: function(name, module) {
            return this._getView(name, module)[1];
        },

        // Convenience private method
        _getField: function(type, view, fallbackTemplate) {
            var prefix = "f." + type + ".";
            var key = prefix + view;
            return [key, this.get(prefix + view) ||
                (_.isUndefined(fallbackTemplate) ? null : this.get(prefix + fallbackTemplate))];
        },

        /**
         * Gets compiled template for a field.
         * @param {String} type Field type.
         * @param {String} view View name.
         * @param {Boolean} fallbackTemplate(optional) Template name to fallback to if template for `view` is not found.
         * if view specific is not found. Defaults to `true`.
         * @return {Function} Compiled template.
         */
        getField: function(type, view, fallbackTemplate) {
            return this._getField(type, view, fallbackTemplate)[1];
        },

        /**
         * Retrieves compiled template for layout
         * @param name
         * @param module
         * @return {Array}
         * @private
         */
        _getLayout: function(name, module) {
            var key = name + (module ? ("." + module) : "");
            return [key, this.get(key)];
        },

        /**
         * Compiles and puts into local storage a view template.
         * @param {String} name View name.
         * @param {String} module Module name.
         * @param {String} src Template source code.
         * @param {Boolean} force Flag indicating if the template must be re-compiled.
         * @return {Function} Compiled template.
         */
        setView: function(name, module, src, force) {
            return this._compile(this._getView(name, module), src, force);
        },

        /**
         * Compiles and puts into local storage a field template.
         * @param {String} type Field type.
         * @param {String} view View name.
         * @param {String} src Template source code.
         * @param {Boolean} force Flag indicating if the template must be re-compiled.
         * @return {Function} Compiled template.
         */
        setField: function(type, view, src, force) {
            // Don't fall back to default template (false flag)
            return this._compile(this._getField(type, view), src, force);
        },

        /**
         * Compiles and stores layout templates
         * @param {String} type Layout Type
         * @param {String} layout Layout Name
         * @param {String} src Raw template source
         * @param {Boolean} force Flag indicating if the template must be re-compiled
         * @return {Function} Compiled template
         */
        setLayout: function(type, layout, src, force) {
            return this._compile(this._getLayout(type, layout), src, force);
        },

        /**
         * Compiles view and field templates from metadata payload and puts them into local storage.
         * This method compiles both view and field templates. The metadata must contain the following sections:
         *
         * <pre>
         * {
         *    // This should now be deprecated
         *    "viewTemplates": {
         *       "detail": HB template source,
         *       "list": HB template source,
         *       // etc.
         *    },
         *
         *    "sugarFields": {
         *        "text": {
         *            "templates": {
         *               "default": HB template source,
         *               "detail": HB template source,
         *               "edit": ...,
         *               "list": ...
         *            }
         *        },
         *        "bool": {
         *           // templates for boolean field
         *        },
         *        // etc.
         *    }
         *
         *    "views": {
         *      "text": {
         *          "templates" {
         *              "view": HB template source...
         *              "view2": HB template source..
         *          }.
         *    }
         * }
         * </pre>
         *
         * @param {Object} metadata Metadata payload.
         * @param {Boolean} force(optional) Flag indicating if the cache is ignored and the templates are to be recompiled.
         */
        set: function(metadata, force) {
            if (metadata.views) {
                _.each(metadata.views, function(view, name) {
                    if (name != "_hash") {
                        _.each(view.templates, function(src, view) {
                            this.setView(name, null, src, force);
                        }, this);
                    }
                }, this);
            }

            if (metadata.fields) {
                _.each(metadata.fields, function(field, type) {
                    if (type != "_hash") {
                        _.each(field.templates, function(src, view) {
                            this.setField(type, view, src, force);
                        }, this);
                    }
                }, this);
            }

            if (metadata.layouts) {
                _.each(metadata.layouts, function(layout, type) {
                    if (type != "_hash") {
                        _.each(layout.templates, function(src, view) {
                            this.setLayout(type, null, src, force);
                        }, this);
                    }
                }, this);
            }
        },

        /**
         * Pre-compiled empty template.
         *
         * @property {Function}
         */
        empty: function() {
            return "";
        }
    };

    app.augment("template", _templateManager);

})(SUGAR.App);
