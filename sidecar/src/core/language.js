(function(app) {
    /**
     * Language Helper. Provides interface to pull language strings out of a language
     * label cache.
     *
     * @class Core.LanguageHelper
     * @singleton
     * @alias SUGAR.App.lang
     */
    app.augment("lang", {
        /**
         * Language hash for labels
         * @property {Object}
         */
        modStrings: {},

        appListStrings: {},
        
        appStrings: {},

        /**
         * Saves a set of labels to its internal cache.
         * @param {String} module Module name
         * @param {Object} data Hash of language labels
         * @param {Boolean} bulk Set to true if doing a bulk save which will not update the app cache.
         * @method
         */
        setLabel: function(module, data, bulk) {
            this.modStrings[module] = data || {};

            if (!bulk) {
                app.cache.set("language:labels", this.modStrings);
            }
        },

        /**
         * Takes multiple modules and sets individually
         * @param {Object} data Language Hash
         */
        setLabels: function(data) {
            _.each(data, function(label, module) {
                this.setLabel(module, label, true);
            }, this);

            app.cache.set("language:labels", this.modStrings);
        },

        /**
         * Sets app list strings (from metadata)
         * @param {Object} data Object of app list strings
         */
        setAppListStrings: function(data) {
            this.appListStrings = data;

            app.cache.set("language:appListStrings", this.appListStrings);
        },

        /**
         * Sets app strings (from metadata)
         * @param {Object} data Object of app strings
         */
        setAppStrings: function(data) {
            this.appStrings = data;

            app.cache.set("language:appStrings", this.appStrings);
        },

        /**
         * Retreives a language label
         * This function searches the module strings first and falls back to the app strings.
         * If no label is found it returns the str arg.
         * @method
         * @param {String} str Label to retreive
         * @param {String} module Module the label belongs to
         * @return {String} Language Label
         * @private
         */
        get: function(str, module) {
            var label= str;
            if (this.modStrings && this.modStrings[module] && this.modStrings[module][str]) {
                label = this.sanitizeString(this.modStrings[module][str]);
            } else if (this.appStrings && this.appStrings[str]) {
                label = this.sanitizeString(this.appStrings[str]);
            }
            return label;
        },

        /**
         * Retreives a App string.
         * NOTE: Not implemented yet
         * @method
         * @param {String} str App string to retreive.
         */
        getAppStrings: function(str) {
            return this.appStrings[str] || false;
        },

        getAppListStrings: function(str) {
            return this.appListStrings[str] || false;
        },

        // We shoudln't need this function :(
        /**
         * Sanitizes the label.
         * @method
         * @param {String} str String to sanitize
         * @return {String} Sanitized String
         * @private
         */
        sanitizeString: function(str) {
            return (typeof str == "string" && (str.lastIndexOf(":") == str.length - 1)) ? str.substring(0, str.length - 1) : str;
        },

        /**
         * Looks up the label provided.
         * @method
         * @param {String} str Name of the lable
         * @param {String} module Name of the module to look up. Leave blank if looking for app strings
         * @return {String} Translated string
         */
        translate: function(str, module) {
            if(typeof module !== 'undefined' && module) {
                return this.get(str, module) || this.getAppStrings(str) || "";
            } else {
                return this.getAppStrings(str);
            }
        }
    });
})(SUGAR.App);
