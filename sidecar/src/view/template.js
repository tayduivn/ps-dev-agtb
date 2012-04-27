(function(app){
    //Pull the precompile header and footer from the node precompile implementation for handlebars
    var _header = "(function() {\n  var template = Handlebars.template, templates = Handlebars.templates = Handlebars.templates || {};\n",
        _footer = '})();',
        _templates = {};

        /**
         * Loads and compiles Handlebars templates.
         * @class View.TemplateManager
         * @singleton
         * @alias SUGAR.App.template
         */
        var _templateManager = {
            //Initialize will pull the compiled templates from local storage and populate Handlebars.templates
            initialize: function() {
                _templates = app.cache.get("templates") || {};
                var src = "";
                _.each(_templates, function(t) {
                    src += t;
                });
                eval(_header + src + _footer);
            },

            /**
             * Compile will put the precompiled version of the template in cache and return the compiled template
             * @param {String} src The actual template source to be compiled
             * @param {String} key An identifier to reference the compiled template at a later time
             * @method
             */
            compile: function(src, key) {
                try {
                    _templates[key] = "templates['" + key + "'] = template(" + Handlebars.precompile(src) + ");\n";
                    app.cache.set("templates", _templates);
                    eval(_header + _templates[key] + _footer);
                } catch (e) {
                    //Bad templates will cause a JS error when they either pre-compile or compile.
                    app.logger.error("Template compilation error; unable to compile " + key + ".\n" + e.message);
                }
                return this.get(key);
            },

            /**
             * Retrieves a compiled handlebars template
             * @method
             * @param {String} key Identifier of the template to be retrieved
             * @return {Function} compiled Handlebars template
             */
            get: function(key, module) {
                var modKey = module + key;
                if (module && Handlebars.templates && Handlebars.templates[modKey])
                    return Handlebars.templates[modKey];

                if (Handlebars.templates && Handlebars.templates[key])
                    return Handlebars.templates[key];
            },

            /**
             * load is used to compile a set of templates sources from metadata
             * @param {Object} metadata an metadata response object with a an object.viewTemplates which is a key => source set of templates to load into memory. Templates that were not previously loaded will be precompiled
             * @param {Boolean} force If true, the cache is ignored and the templates are all recompiled
             * @method
             */
            load: function(metadata, force) {
                if (metadata.viewTemplates) {
                    _.each(metadata.viewTemplates, function(src, key) {
                        if ((key != "_hash") && (!this.get(key) || force)) {
                            this.compile(src, key);
                        }
                    }, this);
                }
            },

            /**
             * This function is called during the app's initialization phase.
             * TODO: Right now metadata is hard coded but will need to be pulled from metadata eventually
             * TODO: Change this name once we remove all the inits
             * @method
             */
            initTemplate: function(instance) {
                //this.load(fixtures.metadata.viewTemplates);
            },

            /**
             * Pre-compiled empty template.
             *
             * @property {Function}
             */
            empty: function() { return ""; }
        };

    app.events.on("app:init", _templateManager.initTemplate, _templateManager);
    app.augment("template", _templateManager);

})(SUGAR.App);