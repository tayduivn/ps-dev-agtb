(function(app) {
    //Metadata that has been loaded from offline storage
    var _metadata = {};
    var _sugarFields = {};
    var fieldTypeMap = {
        varchar: "text",
        name: "text",
        text: "textarea"
    };

    /**
     * The metadata manager is responsible for parsing and returning various metadata to components that request it.
     * @singleton
     * @class MetadataManager
     */
    app.augment("metadata", {
        /**
         * The Metadata Manager get method should be the be the only accessor for metadata.
         *
         * @param {Object} params. Params can have the following properties.
         * <ul>
         *      <li>String module : Module to retrieve metadata for</li>
         *      <li>String type : Type of metadata to retrieve, possible values are
         *          "view", "layout", and "vardef". If not specified, all the metadata
         *          for the given module is returned (Optional)</li>
         *      <li>String view : Specific view to retrieve. If not specified, all views for the given
         *          module are returned.(Optional)</li>
         *      <li>String layout : Specific layout to retrieve. If not specified, all layouts for the
         *          given module are returned.(Optional)</li>
         *      <li>String bean : Specific bean to retrieve. If not specified, the vardefs for the
         *          primary bean are returned.(Optional)</li>
         * </ul>
         * @method
         * @member MetadataManager
         * @return {Object} metadata
         */
        get: function(params) {
            if (params && params.sugarField) {
                return this._getSugarField(params.sugarField);
            }

            // If no parameters are passed in, we return the whole metadata
            if (!params || !params.module) {
                return _metadata;
            }

            if (!params.type){
                return this._getModule(params.module);
            }

            switch(params.type) {
                case "view":
                    return this._getView(params.module, params.view);
                case "layout":
                    return this._getLayout(params.module, params.layout);
                case "vardef":
                    return this._getVardef(params.module);
                case "fieldDef":
                    return this._getFieldDef(params.module, params.field);
                default:
            }
        },

        /**
         * Function that attempts to retrieve sugarFields from offline caches
         * If its not there, it will make a server call to start a sync
         * The sync will block the app
         * TODO add infinite loop prevetion in sync
         * @param {String} module of metadata
         * @param {String} type of metadata
         * @return {Object} metadata
         * @private
         */
        _getModule: function(module, type) {
            if (typeof(_metadata[module]) == "undefined") {
                _metadata[module] = app.cache.get("metadata." + module);
                if (typeof(_metadata[module]) == "undefined") {
                    app.sync();
                    return null;
                }
            }

            if (!type) {
                return _metadata[module];
            }

            if (typeof(_metadata[module][type]) == "undefined") {
                app.sync();
                return null;
            }

            return _metadata[module][type];
        },

        /**
         * @param {String} field Name of field metadata
         * @return {Object} metadata
         * @private
         */
        _getSugarField: function(field) {

            // init results
            var result, views;
            var name = fieldTypeMap[field.type] || field.type;

            if (!name) {
                app.logger.error("No field name provided to getSugarField");
                return null;
            }

            // get sugarfield from app cache if we dont have it in memory
            if (typeof(_sugarFields[name]) == "undefined") {
                _sugarFields[name] = app.cache.get("sugarFields." + name);
            }

            if (_sugarFields[name]) {
                views = _sugarFields[name].views || _sugarFields[name];
                var viewName = field.viewName || field.view;
                //No viewname means return the full metadata for this field
                if (!viewName) {
                    result = _sugarFields[name];
                } else {
                    // assign fields to results if set
                    if (viewName && views[viewName]) {
                        result = views[viewName];
                        // fall back to detailview if field for this view doesnt exist
                    } else if (views && views['default']) {
                        result = views['default'];
                        //fall back to base field detailview if none of the above exist
                    }
                }

            }

            if (!result && _sugarFields.text && _sugarFields.text.views['default']) {
                result = _sugarFields.text.views['default'];
            }
            //Could not get valid view data for this field
            else if (!result) {
                app.Sync();
                return null;
            }

            return result;
        },

        /**
         * Returns metadata for Views
         * @private
         * @param {String} module Name of module to retrieve from
         * @param {String} view Optional name of view to get
         * @return {Object} metadata
         */
        _getView: function(module, view) {
            var views = this._getModule(module, "views");
            if (views !== null) {
                if (view) {
                    if (typeof(views[view]) != "undefined")
                        return views[view];
                } else {
                    return views;
                }
            }
            return null;
        },

        /**
         * Returns metadat for Layouts
         * @private
         * @param {String} module Name of module to retrieve from
         * @param {String} layout Name of layout to retrieve from
         * @return {Object} metadata
         */
        _getLayout: function(module, layout) {
            var layouts = this._getModule(module, "layouts");

            if (layouts !== null) {
                if (layout) {
                    if (typeof(layouts[layout]) != "undefined")
                        return layouts[layout];
                } else {
                    return layouts;
                }
            }

            return null;
        },

        /**
         * Returns vardef
         * @private
         * @param {String} module Module name
         * @return {Object} vardef
         */
        _getVardef: function(module) {
            return this._getModule(module, "fields");
        },

        /**
         * Returns Fielddef metadata
         * @param {String} module Module name
         * @param {String} field Name of field
         * @return {Object} metadata
         * @private
         * @method
         */
        _getFieldDef: function(module, field) {
            var vardef = this._getVardef(module);
            return vardef ? vardef[field] : null;
        },

        // set is going to be used by the sync function and will transalte
        // from server format to internal format for metadata

        /**
         * Set the metadata.
         * By default this function is used by MetadataManager to translate server responses into metadata
         * useable internally.
         * @param {Object} data Metadata
         * @param {String} key Metadata identifier
         * @method
         */
        set: function(data, key) {
            key = key || "metadata";
            if (data.modules) {
                _.each(data.modules, function(entry, module) {
                    _metadata[module] = entry;
                    app.cache.set(key + "." + module, entry);
                });
            }
            if (data.sugarFields) {
                _.each(data.sugarFields, function(entry, module) {
                    _sugarFields[module] = entry;
                    app.cache.set(key + "." + module, entry);
                });
            }
            //TODO add template support
        },

        /**
         * Syncs metadata from server using the Api wrapper. Saves the metadata to the manager.
         * @method
         * @param {Function} callback Callback function to be executed after a sync
         */
        sync: function(callback) {
            var self = this;
            app.api.getMetadata([], [], {
                success: function(metadata) {
                    self.set(metadata);
                    callback.call(self, null, metadata);
                },
                error: function(error) {
                    console.log("Error fetching metadata");
                    console.log(error);
                    callback.call(self, error);
                }
            });
        }
    });
})(SUGAR.App);