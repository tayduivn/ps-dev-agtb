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
         * Retrieves a string for a given key.
         *
         * This function searches the module strings first and falls back to the app strings.
         *
         * @param {String} key Key of the string to retrieve.
         * @param {String} module(optional) Module the label belongs to.
         * @return {String} String for the given key or the `key` parameter if the key is not found in language pack.
         */
        get: function(key, module) {
            return this._get("modStrings", key, module) ||
                   this._get("appStrings", key) ||
                   key;
        },

        /**
         * Retrieves a string for a given key and compile it with the given context.
         *
         * This function searches the module strings first and falls back to the app strings.
         *
         * @param {String} key Key of the string to retrieve.
         * @param {String} ctx(optional) Context to be pushed to the template.
         * @return {String} compiled html.
         */
        getCompiled: function(key, ctx, module) {
            ctx = ctx || {};
            var tpl = app.template.compile(key, this.get(key, module));
            return tpl(ctx);
        },

        /**
         * Retreives an application string for a given key.
         * @param {String} key Key of the string to retrieve.
         * @return {String} String for the given key or the `key` parameter if the key is not found in the language pack.
         */
        getAppString: function(key) {
            return this._get("appStrings", key) || key;
        },

        /**
         * Retrieves an application list string or object.
         * @param {String} key Key of the string to retrieve.
         * @param {String} defaultValue(optional) Value to return if the key is not found.
         * @return {Object/String} String or object for the given key.
         */
        getAppListStrings: function(key, defaultValue) {
            return this._get("appListStrings", key) || defaultValue;
        },

        /**
         * Retrieves a string of a given type.
         * @param {String} type Type of string pack: `appStrings`, `appListStrings`, `modStrings`.
         * @param {String} key Key of the string to retrieve.
         * @param {String} module(optional) Module the label belongs to.
         * @return {String} String for the given key.
         * @private
         */
        _get: function(type, key, module) {
            var bundle = app.metadata.getStrings(type);
            bundle = module ? bundle[module] : bundle;
            return bundle ? this._sanitize(bundle[key]) : null;
        },

        /**
         * Sanitizes a string.
         *
         * This function strips trailing colon.
         *
         * @param {String} str String to sanitize.
         * @return {String} Sanitized string or `str` parameter if it's not a string.
         * @private
         */
        _sanitize: function(str) {
            return (_.isString(str) && (str.lastIndexOf(":") == str.length - 1)) ?
                    str.substring(0, str.length - 1) : str;
        }

    });

})(SUGAR.App);
