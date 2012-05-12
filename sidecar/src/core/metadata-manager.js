(function(app) {
    // Key prefix used to identify metadata in the local storage.
    var _keyPrefix = "md:";
    var _modulePrefix = "m:";
    var _fieldPrefix = "f:";
    var _langPrefix = "lang:";

    // Metadata that has been loaded from offline storage (memory cache)
    // Module specific metadata
    var _metadata = {};
    // Field definitions
    var _fields = {};
    // String packs
    var _lang = {};
    // Other
    var _app = {};

    function _get(key) {
        return app.cache.get(_keyPrefix + key);
    }

    function _set(key, value) {
        app.cache.set(_keyPrefix + key, value);
    }

    function _setData(container, property, prefix, data) {
        if (data[property]) {
            container[property] = data[property];
            _set(prefix + property, data[property]);
        }
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
        var plural = type + 's';

        _.each(entry[plural], function (obj, name) {
            if (type === "view" && obj && obj.template) {
                app.template.setView(name, module, obj.template, true);
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
            _.each(module.views, function(view) {
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
            if (!_app.moduleList) {
                _app.moduleList = _get("moduleList");
            }

            if (_app.moduleList) delete _app.moduleList._hash;
            return _app.moduleList || {};
        },

        /**
         * Gets language strings for a given type.
         * @param {String} type Type of string pack: `appStrings`, `appListStrings`, `modStrings`.
         * @return Dictionary of strings.
         */
        getStrings: function(type) {
            var r = _lang[type];
            if (!r) {
                r = _get(_langPrefix + type);
            }
            return r || {};
        },

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

            _setData(_app, "moduleList", "", data);

            _setData(_lang, "appListStrings", _langPrefix, data);
            _setData(_lang, "appStrings", _langPrefix, data);
            _setData(_lang, "modStrings", _langPrefix, data);

            _setData(_app, "_hash", "", data);

            app.template.set(data, true);
        },

        /**
         * Gets metadata hash.
         * @return {String} Metadata hash tag.
         */
        getHash: function() {
            return _app._hash || _get("_hash") || "";
        },

        /**
         * Syncs metadata from the server. Saves the metadata to the local cache.
         * @param {Function} callback(optional) Callback function to be executed after sync completes.
         */
        sync: function(callback) {
            var self = this;

            app.api.getMetadata(self.getHash(), app.config.metadataTypes, [], {
                success: function(metadata, textStatus, jqXHR) {
                    if (jqXHR.status == 304) { // Our metadata is up to date so we do nothing.
                        app.logger.debug("Metadata is up to date");
                    } else if (jqXHR.status == 200) { // Need to update our app with new metadata.
                        app.logger.debug("Metadata is out of date");
                        self.set(metadata);
                    }

                    if (callback) {
                        callback.call(self, null, metadata);
                    }
                },
                error: function(error) {
                    app.logger.error("Error fetching metadata " + error);

                    if (callback) {
                        callback.call(self, error);
                    }
                }
            });
        }
    });

})(SUGAR.App);

