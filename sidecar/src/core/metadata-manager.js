(function(app) {
    // Key prefix used to identify metadata in the local storage.
    var _keyPrefix = "md:";
    var _modulePrefix = "m:";
    var _fieldPrefix = "f:";
    var _appPrefix = "a:";

    // Metadata that has been loaded from offline storage (memory cache)
    var _app = {};
    var _metadata = {};
    var _fields = {};

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
         * @param {Object} type Field type.
         * @return {Object} Metadata for the specified field type.
         */
        getField: function(type) {
            var metadata = _fields[type];
            if (!metadata) {
                _fields[type] = _get(_fieldPrefix + type);
                metadata = _fields[type];
            }

            // Fall back to plain text field
            if (!metadata) {
                metadata = _fields.text;
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

        /**
         * Gets module list
         * @return {Object}
         */
        getModuleList: function() {
           var result =  {};
            if (_app.moduleList) {
                result = _app.moduleList;
            } else {
                _app.moduleList=_get(_appPrefix+"moduleList");
                result = _app.moduleList;
            }

            if(result._hash) {
                delete result._hash;
            }

            return result;
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
                    _fields[module] = entry;
                    _set(_fieldPrefix + module, entry);
                });
            }

            if (data.moduleList) {
                _app.moduleList = data.moduleList;
                _set(_appPrefix+"moduleList", data.moduleList);
            }

            //TODO add template support
        },

        /**
         * Syncs metadata from the server. Saves the metadata to the local cache.
         * @param {Function} callback(optional) Callback function to be executed after sync completes.
         */
        sync: function(callback) {
            var self = this;
            app.api.getMetadata(app.config.metadataTypes, [], {
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