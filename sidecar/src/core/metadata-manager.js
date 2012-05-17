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

    function _setMeta(container, property, prefix, meta) {
        if (meta[property]) {
            container[property] = meta[property];
            _set(prefix + property, meta[property]);
        }
    }

    function _getMeta(container, property, prefix, deleteHash) {
        if (!container[property]) {
            container[property] = _get(prefix + property);
        }

        if (deleteHash && container[property]) delete container[property]._hash;
        return container[property];
    }

     // Initializes custom layouts/views templates and controllers
    function _initCustomComponents(module, moduleName) {
        _.each(["layout", "view"], function(type) {
            _.each(module[type + 's'], function (def, name) {
                if (type === "view" && def.template) { // Only views can have templates
                    app.template.setView(name, moduleName, def.template, true);
                }
                if (def.controller) { // Both layouts and views can have controllers
                    app.view.declareComponent(type, name, moduleName, def.controller, def.meta.type);
                }
            });
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
         * Specifies correspondence between field types and field widget types.
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
            // Fall back to plain text field
            return _getMeta(_fields, type, _fieldPrefix) || _fields.text;
        },

        /**
         * Gets view metadata.
         * @param {String} module Module name.
         * @param {String} view(optional) View name.
         * @return {Object} View metadata if view name is specified. Otherwise, metadata for all views of the given module.
         */
        getView: function(module, view) {
            var metadata = this.getModule(module, "views");
            if (metadata && metadata[view]) {
                metadata = metadata[view].meta;
            }

            return metadata;
        },

        /**
         * Parses through an array of components looking for any attached listeners
         *
         * Expects the following metadata structure
         *      metadata[layout].meta.components[].layout.components[].listeners
         *
         * @param comp {Array} of components
         * @param listenerArray {Array} to push listeners to
         * @return {*}
         */
        parseComponentsForEvents : function( comp , listenerArray )  {
            var item;

            for( var i in comp )
            {
                item = comp[i];

                if(item.hasOwnProperty("listeners"))
                {
                    return listenerArray[item.view] = item.listeners;
                }
                else if( item.hasOwnProperty("layout") && item.layout.hasOwnProperty("components"))
                {
                    this.parseComponentsForEvents( item.layout.components , listenerArray );
                }
                else
                {
                    return;
                }
            }
        },

        /**
         * Returns any listeners outlined in the layout metadata
         *
         * @param module {String} which module in the metadata to look in
         * @param layout {String} which layout in the module's metadata to look in
         * @param view {String} which view in the layout's metadata to look in
         * @return {*}
         */
        getListeners: function(module, layout, view )  {
            var retListeners = null;
            if( _metadata[module].layouts.hasOwnProperty(layout) &&
                _metadata[module].layouts[layout].meta.hasOwnProperty("listeners") &&
                _metadata[module].layouts[layout].meta.listeners.hasOwnProperty(view) )  {
                retListeners = _metadata[module].layouts[layout].meta.listeners[view];
            }
            return retListeners;
        },

        /**
         * Returns the currentLayout (list, edit, detail, etc)
         *
         * @return {*} if currentLayout has loaded yet, this will return a String value
         */
        getCurrentLayout: function() {
            return _metadata.currentLayout;
        },

        /**
         * Gets layout metadata.
         * @param {String} module Module name.
         * @param {String} layout(optional) Layout name.
         * @return {Object} Layout metadata if layout name is specified. Otherwise, metadata for all layouts of the given module.
         */
        getLayout: function(module, layout) {
            // reset currentLayout
            _metadata.currentLayout = null;
            var metadata = this.getModule(module, "layouts");

            var listeners = {};
            if( metadata && metadata.hasOwnProperty(layout) && metadata[layout].meta.components )  {
                this.parseComponentsForEvents( metadata[layout].meta.components , listeners );
            }

            if (metadata && metadata[layout]) {
                metadata = metadata[layout].meta;

                // Set currentLayout
                _metadata.currentLayout = layout;
            }

            if( !jQuery.isEmptyObject(listeners) ) {
                metadata.listeners = listeners;
            }

            return metadata;
        },

        /**
         * Gets module list
         * @return {Object}
         */
        getModuleList: function() {
            return _getMeta(_app, "moduleList", "", true) || {};
        },

        /**
         * Gets language strings for a given type.
         * @param {String} type Type of string pack: `appStrings`, `appListStrings`, `modStrings`.
         * @return Dictionary of strings.
         */
        getStrings: function(type) {
            return _getMeta(_lang, type, _langPrefix) || {};
        },

        /**
         * Gets ACLs.
         *
         * @return Dictionary of ACLs.
         */
        getAcls: function() {
            return _getMeta(_app, "acl", "") || {};
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
                    _initCustomComponents(entry, module);

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

            _setMeta(_app, "moduleList", "", data);

            _setMeta(_lang, "appListStrings", _langPrefix, data);
            _setMeta(_lang, "appStrings", _langPrefix, data);
            _setMeta(_lang, "modStrings", _langPrefix, data);

            _setMeta(_app, "acl", "", data);

            _setMeta(_app, "_hash", "", data);

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

