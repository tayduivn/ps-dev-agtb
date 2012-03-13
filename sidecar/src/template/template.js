(function(app){
    //Pull the precompile header and footer from the node precompile implementation for handlebars
    var header = "(function() {\n  var template = Handlebars.template, templates = Handlebars.templates = Handlebars.templates || {};\n";
    var footer = '})();';
    var templates = {};
    var sources;

    app.augment("template", {
        //Initialize will pull the compiled templates from local storage and populate Handlebars.templates
        initialize : function(){
            templates = app.cache.get("templates") || {};
            var src = "";
            _.each(templates, function(t){
                src += t;
            });
            eval(header + src + footer);
        },

        /**
         * Compile will put the precompiled version of the template in cache and return the compiled template
         * @method
         */
        compile : function(src, key) {
            try {
                templates[key] = "templates['" + key + "'] = template(" + Handlebars.precompile(src) + ");\n";
                app.cache.set("templates", templates);
                eval(header + templates[key] + footer);
            }catch(e) {
                //Bad templates will cause a JS error when they either pre-compile or compile.
                app.logger.error("Template compilation error; unable to compile " + key + ".\n" + e.message);
            }
            return this.get(key);
        },

        get : function(key) {
            if (Handlebars.templates && Handlebars.templates[key])
                return Handlebars.templates[key];
        },
        /**
         * load is used to compile a set of templates sources
         * @param Object templates a key => source set of templates to load into memory. Templates that were not previously loaded will be precompiled
         * @param Boolean force if true, the cache is ignored and the templates are all recompiled
         */
        load : function(templates, force) {
            _.each(templates, function(src, key){
                if (!this.get(key) || force);
                    this.compile(src, key);
            }, this);
        },

        /**
         * This function is called during the app's initialization phase.
         * TODO: Right now metadata is hard coded but will need to be pulled from metadata eventually
         * @method
         */
        init: function() {
            this.load(fixtures.templates);
        }
    });
})(SUGAR.App);