(function(app) {
    // Key prefix used to identify metadata in the local storage.
    var _keyPrefix = "md:";
    var _modulePrefix = "m:";
    var _relPrefix = "r:";
    var _fieldPrefix = "f:";
    var _layoutPrefix = "l:";
    var _viewPrefix = "v:";
    var _langPrefix = "lang:";

    // TODO: Maybe just have this all in _metadata?

    // Metadata that has been loaded from offline storage (memory cache)
    // Module specific metadata
    var _metadata = {};
    // Relationship definitions
    var _relationships = {};
    // Field definitions
    var _fields = {};
    // View definitions
    var _views = {};
    // Layout definitions
    var _layouts = {};
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
            _.each(module[type + 's'], function(def, name) {
                if (type === "view" && def.template) { // Only views can have templates
                    app.template.setView(name, moduleName, def.template, true);
                }
                if (def.controller) { // Both layouts and views can have controllers
                    app.view.declareComponent(type, name, moduleName, def.controller, def.meta.type, true);
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
            currency: "text",
            // viewdefs use "link" type to denote the fact the field is actually a url instead of using type "url" from vardefs
            // in the main product there are identical templates for both url and link
            // note that if someone puts a "real" link field (relationship link) on a view, it'll break the mapping
            // this should be fixed on the server because "link" type on a view was meant to be something like
            // a display type, not widget type. In other words, it tells the app to make the url a hyperlink
            // instead of displaying it as a text string.
            link: "url"
        },

        /**
         * Patches view fields' definitions.
         * @param moduleName Module name
         * @param module Module definition
         * @private
         */
        _patchMetadata: function(moduleName, module) {
            if (!module || module._patched === true) return module;
            var self = this;
            _.each(module.views, function(view) {
                if (view.meta) {
                    _.each(view.meta.panels, function(panel) {
                        _.each(panel.fields, function(field, fieldIndex) {
                            var name = _.isString(field) ? field : field.name;
                            var fieldDef = module.fields[name];
                            if (!_.isEmpty(fieldDef)) {
                                // Create a definition if it doesn't exist
                                if (_.isString(field)) {
                                    field = { name: field };
                                }

                                // Flatten out the viewdef, i.e. put 'displayParams' onto the viewdef
                                // TODO: This should be done on the server-side on my opinion

                                if (_.isObject(field.displayParams)) {
                                    _.extend(field, field.displayParams);
                                    delete field.displayParams;
                                }

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
            var metadata;
            if (module) {
                metadata = _metadata[module];

                // Load metadata in memory if it's not there yet
                if (!metadata) {
                    _metadata[module] = this._patchMetadata(module, _get(module));
                    metadata = _metadata[module];
                }

                if (metadata && type) {
                    metadata = metadata[type];
                }
            }

            return metadata;
        },

        /**
         * Gets a relationship definition.
         * @param {String} name Relationship name.
         * @return {Object} Relationship metadata.
         */
        getRelationship: function(name) {
            return _getMeta(_relationships, name, _relPrefix);
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
            } else if (_views[view]) {
                metadata = _views[view];
            }

            if (!metadata) {
                app.logger.info("No view found for " + view);
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

            // Check to see if there is a module layout
            if (metadata && metadata[layout]) {
                metadata = metadata[layout].meta;
            } else if (_layouts[layout]) { // Look for a module non-specific layout
                metadata = _layouts[layout].meta;
            }

            if (!metadata) {
                app.logger.info("No layout found for " + layout);
            }

            return metadata;
        },

        /**
         * Gets module list
         * @return {Object}
         */
        getModuleList: function(opts) {
            var meta = _getMeta(_app, "moduleList", "", true) || {};

            /**
             * @cfg {Boolean} opts.visible Set true if you want to return only module lists that the user has access to.
             */
            if (opts && opts.visible && app.config && app.config.displayModules) {
                meta = _.intersection(_.toArray(meta), app.config.displayModules);
            }

            return meta;
        },

        /**
         * Gets module list as delimited string
         * @param {String} The delimiter to use.
         * @param {Boolean} true if only wants modules loaded by this application. 
         * @return {Object}
         */
        getDelimitedModuleList: function(delimiter, visible) {
            if(!delimiter) return null;
            return _.toArray(this.getModuleList({visible: (visible?visible:false)})).join(delimiter);
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
         * Gets Config.
         *
         * @return Dictionary of Configs.
         */
        getConfig: function() {
            return _getMeta(_app, "config", "") || {};
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

            if (data.relationships) {
                _.each(data.relationships, function(entry, relationship) {
                    _relationships[relationship] = entry;
                    _set(_relPrefix + relationship, entry);
                });
            }

            if (data.fields) {
                _.each(data.fields, function(entry, type) {
                    _fields[type] = entry;
                    _set(_fieldPrefix + type, entry);
                    if (entry.controller) {
                        app.view.declareComponent("field", type, null, entry.controller, null, true);
                    }
                });
            }

            if (data.views) {
                _.each(data.views, function(entry, type) {
                    _views[type] = entry;
                    _set(_viewPrefix + type, entry);
                    if (entry.controller) {
                        app.view.declareComponent("view", type, null, entry.controller, null, true);
                    }
                });
            }

            if (data.layouts) {
                _.each(data.layouts, function(layout, type) {
                    _layouts[type] = layout;
                    _set(_layoutPrefix + type, layout);
                    if (layout.controller) {
                        app.view.declareComponent("layout", type, null, layout.controller, null, true);
                    }
                });
            }

            if (data.config) {
                _.each(data.config, function(value, key) {
                    if (!app.config) {
                        app.config = {};
                    } else {
                        app.config[key] = value;
                    }
                });
                _setMeta(_app, "config", "", data);
            }

            _setMeta(_app, "moduleList", "", data);

            _setMeta(_lang, "appListStrings", _langPrefix, data);
            _setMeta(_lang, "appStrings", _langPrefix, data);
            _setMeta(_lang, "modStrings", _langPrefix, data);

            _setMeta(_app, "acl", "", data);

            _setMeta(_app, "_hash", "", data);

            app.template.set(data, true);

            // Cache the metadata
            if (app.config.env == "dev") {
                this.data = data;
            }
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
         * @param {Object} options(optional) Sync call options currently supports public:true to get public metadata.
         */
        sync: function(callback, options) {
            options = options || {};
            var self = this;
            var metadataTypes = app.config.metadataTypes || [];
            app.api.getMetadata(self.getHash(), metadataTypes, [], {
                success: function(metadata, textStatus, jqXHR) {
                    if (jqXHR.status == 304) { // Our metadata is up to date so we do nothing.
                        app.logger.trace("Metadata is up to date");
                    } else if (jqXHR.status == 200) { // Need to update our app with new metadata.
                        app.logger.trace("Metadata is out of date");
                        self.set(metadata);
                    }

                    if (callback) {
                        callback.call(self);
                    }
                },
                error: function(error) {
                    app.logger.error("Error fetching metadata");
                    app.error.handleHttpError(error);
                    if (callback) {
                        callback.call(self, error);
                    }
                }
            }, options);
        }
    });

})(SUGAR.App);

