(function(app) {
    // Key prefix used to identify metadata in the local storage.
    var _keyPrefix = "md:";
    var _modulePrefix = "m:";
    var _fieldPrefix = "f:";
    // Metadata that has been loaded from offline storage (memory cache)
    var _metadata = {};
    var _sugarFields = {};
    var _fieldTypeMap = {
        varchar: "text",
        name: "text",
        text: "textarea",
        decimal: "float",
        currency: "text"
    };

    function _get(key) {
        return app.cache.get(_keyPrefix + key);
    }

    function _set(key, value) {
        app.cache.set(_keyPrefix + key, value);
    }

    /**
     * The metadata manager is responsible for parsing and returning various metadata to components that request it.
     * @class Core.MetadataManager
     * @singleton
     * @alias SUGAR.App.metadata
     */
    app.augment("metadata", {

        /**
         * Gets metadata for all modules.
         * @return {Object} Metadata for all modules.
         */
        getModules: function() {
            var s = _get("modules");
            if (s) {
                var modules = s.split(",");
                _.each(modules, function(module) {
                    if (!_metadata[module]) {
                        _metadata[module] = _get(_modulePrefix + module);
                    }
                });
            }
            return _metadata;
        },

        /**
         * Gets module metadata.
         * @param {String} module Module name.
         * @param {String} type(optional) Metdata type.
         * @return {Object} Module metadata of specific type if type is specified. Otherwise, module's overall metadata.
         */
        getModule: function(module, type) {
            var metadata = _metadata[module];

            // Load metadata in memory if it's not there yet
            if (!metadata) {
                _metadata[module] = _get(module);
                metadata = _metadata[module];
            }

            if (metadata && type) {
                metadata = metadata[type];
            }
            return metadata;
        },

        /**
         * Gets field widget metadata.
         * @param {Object} field Field definition.
         * @return {Object} metadata
         */
        getField: function(field) {
            var metadata;
            var name = _fieldTypeMap[field.type] || field.type;
            var viewName = field.viewName || field.view;

            if (!name) {
                app.logger.warn("Unknown sugar field type: " + field.type);
                return null;
            }

            metadata = _sugarFields[name];
            // get sugarfield from app cache if we dont have it in memory
            if (!metadata) {
                _sugarFields[name] = _get(_fieldPrefix + name);
                metadata = _sugarFields[name];
            }

            if (metadata) {
                var views = metadata.views;
                if (views && viewName) {
                    metadata = views[viewName];
                    if (!metadata) {
                        // fall back to default view if view for this field doesnt exist
                        metadata = views['default'];
                    }
                }
                // TODO: This is temp hack for metadata that doesn't contain 'views' section
                else if (viewName) {
                    var t = metadata[viewName];
                    if (t) {
                        metadata = t;
                    }
                    else {
                        // fall back to default view if view for this field doesnt exist
                        metadata = metadata['default'];
                    }
                }

            }

            if (!metadata && _sugarFields.text && _sugarFields.text.views['default']) {
                metadata = _sugarFields.text;
            }

            return metadata;
        },

        /**
         * Gets view metadata.
         * @param {String} module Module name.
         * @param {String} view(optional) View name.
         * @return {Object} View metadata if view name is specified. Otherwise, metadata for all views of the given module.
         */
        getView: function(module, view) {
            var metadata = this.getModule(module, "views");
            if (metadata && view) {
                metadata = metadata[view];
            }

            return metadata;
        },

        /**
         * Gets layout metadata.
         * @param {String} module Module name.
         * @param {String} layout(optional) Layout name.
         * @return {Object} Layout metadata if layout name is specified. Otherwise, metadata for all layouts of the given module.
         */
        getLayout: function(module, layout) {
            var metadata = this.getModule(module, "layouts");
            if (metadata && layout) {
                metadata = metadata[layout];
            }

            return metadata;
        },

        // set is going to be used by the sync function and will transalte
        // from server format to internal format for metadata

        /**
         * Sets the metadata.
         *
         * By default this function is used by MetadataManager to translate server responses into metadata
         * usable internally.
         * @param {Object} data Metadata payload returned by the server.
         */
        set: function(data) {
            if (data.modules) {
                var modules = [];
                _.each(data.modules, function(entry, module) {
                    _metadata[module] = entry;
                    _set(_modulePrefix + module, entry);
                    modules.push(module);
                });
                _set("modules", modules.join(","));
            }

            if (data.sugarFields) {
                _.each(data.sugarFields, function(entry, module) {
                    _sugarFields[module] = entry;
                    _set(_fieldPrefix + module, entry);
                });
            }

            if (data.appListStrings) {
                app.lang.setAppListStrings(data.appListStrings);
            }

            if (data.appStrings) {
                app.lang.setAppStrings(data.appStrings);
            }

            //TODO add template support
        },

        /**
         * Syncs metadata from the server. Saves the metadata to the local cache.
         * @param {Function} callback(optional) Callback function to be executed after sync completes.
         */
        sync: function(callback) {
            var self = this;
            app.api.getMetadata([], [], {
                success: function(metadata) {
                    self.set(metadata);
                    if (callback) {
                        callback.call(self, null, metadata);
                    }
                },
                error: function(error) {
                    app.logger.error("Error fetching metadata");
                    app.logger.error(error);
                    if (callback) {
                        callback.call(self, error);
                    }
                }
            });
        }
    });

})(SUGAR.App);