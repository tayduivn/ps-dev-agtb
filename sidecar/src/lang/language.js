(function(app) {
    app.augment("lang", {
        langmap: {},
        baseUrl: "cache/jsLanguage",

        setLabel: function(module, data, bulk) {
            this.langmap[module] = data || {};

            if (!bulk) {
                app.cache.set("language:labels", this.langmap);
            }
        },

        // Takes multiple modules and sets individually
        setLabels: function(data) {
            _.each(data, function(label, module) {
                this.setLabel(module, label, true);
            }, this);

            app.cache.set("language:labels", this.langmap);
            console.log(app)
        },

        get: function(str, module) {
            return this.sanitizeString(this.langmap[module][str]) || false;
        },

        getAppStrings: function(str) {
            return this.appStrings[str] || false;
        },

        // We shoudln't need this function :(
        sanitizeString: function(str) {
            return (typeof str == "string" && (str.lastIndexOf(":") == str.length - 1)) ? str.substring(0, str.length - 1) : str;
        },

        translate: function(str, module) {
            return this.get(str, module) || this.getAppStrings(str) || "";
        }
    });
})(SUGAR.App);