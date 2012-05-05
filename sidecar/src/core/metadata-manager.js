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
     * Initializes custom templates and controller if supplied upstream.
     * @param {Object} entry - a module
     * @param {String} type - 'view'||'layout'
     * @param {String} module name
     * @private
     * @ignore
     */
    function _initCustomTemplatesAndComponents(entry, type, module) {
        var plural = type + 's',
            templateKey;

        _.each(entry[plural], function (obj, name) {
            if (obj && obj.template) {
                templateKey = name + '.' + module.toLowerCase();
                app.template.compile(obj.template, templateKey);
            }
            if (obj && obj.controller) {
                app.view.declareComponent(type, name, module, obj.controller, obj.meta.type);
            }
        });

    }

    /**
     * The metadata manager is responsible for parsing and returning various metadata to components that request it.
     * @class Core.MetadataManager
     * @singleton
     * @alias SUGAR.App.metadata
     */
    app.augment("metadata", {

        /**
         * Map of fields types.
         *
         * Specifies correspondence between module field types and field widget types.
         *
         * - `varchar`, `name`, `currency` are mapped to `text` widget
         * - `text` - `textarea`
         * - `decimal` - `float`
         */
        fieldTypeMap: {
            varchar: "text",
            name: "text",
            text: "textarea",
            decimal: "float",
            currency: "text"
        },

        /**
         * Patches view fields' definitions.
         * @param moduleName Module name
         * @param module Module definition
         * @private
         */
        _patchMetadata: function (moduleName, module) {
            if (!module || module._patched === true) return module;
            var self = this;
            _.each(module.views, function(view, viewName) {
                if(view.meta) {
                    _.each(view.meta.panels, function(panel) {
                        _.each(panel.fields, function(field, fieldIndex) {
                            var name = _.isString(field) ? field : field.name;
                            var fieldDef = module.fields[name];
                            if (!_.isEmpty(fieldDef)) {
                                // Create a definition if it doesn't exist
                                if (_.isString(field)) {
                                    field = { name: field };
                                }

                                // Patch label
                                field.label = field.label || fieldDef.vname || fieldDef.name;
                                // Assign type
                                field.type = field.type || fieldDef.type;
                                // Patch type
                                field.type = self.fieldTypeMap[field.type] || field.type;

                                panel.fields[fieldIndex] = field;
                            }
                            else {
                                // Ignore view fields that don't have module field definition
                                //app.logger.warn("Field #" + fieldIndex + " '" + name + "' in " + viewName + " view of module " + moduleName + " has no vardef");
                            }
                        });
                    });
                }
            });
            module._patched = true;
            return module;
        },

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
                _metadata[module] = this._patchMetadata(module, _get(module));
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
                if(metadata[view] && metadata[view].meta) {
                    metadata = metadata[view].meta;
                }
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

            // TODO: Server returns empty array for mobile platform
            // REMOVE THIS HACK ONCE RESOLVED
            //------------- HACK START ----------------------
            if (_.isArray(metadata)) {
                metadata = undefined;
            }
            //------------- HACK END ----------------------

            if (metadata && layout) {
                if(metadata[layout] && metadata[layout].meta) {
                    metadata = metadata[layout].meta;
                } 
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

            if(result && result._hash) {
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
                    _metadata[module] = this._patchMetadata(module, entry);
                    _set(_modulePrefix + module, entry);
                    modules.push(module);

                    // Compile templates and declare components for custom layouts and views
                    _initCustomTemplatesAndComponents(entry, 'view', module);
                    _initCustomTemplatesAndComponents(entry, 'layout', module);

                   }, this);
                _set("modules", modules.join(","));
            }

            if (data.sugarFields) {
                _.each(data.sugarFields, function(entry, type) {
                    _fields[type] = entry;
                    _set(_fieldPrefix + type, entry);
                    if (entry.controller) {
                        app.view.declareComponent("field", type, null, entry.controller);
                    }
                });
            }

            if (data.moduleList) {
                _app.moduleList = data.moduleList;
                _set(_appPrefix + "moduleList", data.moduleList);
            }
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

