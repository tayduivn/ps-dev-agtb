/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
(function(app) {
    app.events.on('app:init', function() {
        var BeanOverrides, Link, VirtualCollection;

        /**
         * @class Link
         * @extends Data.BeanCollection
         *
         * Manages a relationship on a model.
         *
         * It provides the ability to set up {@link Data.Bean beans} to be related
         * to and unrelated from another record when the record is synchronized. See
         * {@link Link#linkRecord} and {@link Link#unlinkRecord}.
         */
        Link = app.BeanCollection.extend({
            initialize: function(models, options) {
                options || (options = {});

                if (options.module) {
                    this.module = options.module;
                    delete options.module;
                }

                this.defaults = [];

                app.BeanCollection.prototype.initialize.call(this, models, options);
            },

            /**
             * Returns `true` if the model has already been linked; `false` if
             * not.
             *
             * @param {Data.Bean} model
             * @return {boolean}
             */
            isDefault: function(model) {
                return _.contains(this.defaults, model.id);
            },

            /**
             * Returns an object that contains all of the changes to be made to
             * the relationship.
             *
             * Used by {@link VirtualCollection#toJSON} to produce the JSON for
             * linking and unlinking records in conjunction with saving a
             * record.
             *
             *     @example
             *     {
             *         "add":[1,2],
             *         "delete":[3]
             *     }
             *
             * @return {Object}
             */
            transpose: function() {
                var actions;

                actions = this.reduce(function(json, model) {
                    switch (model.get('_action')) {
                        case 'delete':
                            json.delete.push(model.id);
                            break;
                        default:
                            json.add.push(model.id);
                    }

                    return json;
                }, {add: [], delete: []});

                if (actions.add.length === 0) {
                    delete actions.add;
                }

                if (actions.delete.length === 0) {
                    delete actions.delete;
                }

                return actions;
            },

            /**
             * Adds a model to be linked.
             *
             * An `_action` attribute will be set to `create` on the model if
             * the model is a new {@link Data.Bean} to link. The `_action`
             * attribute will be set to `update` on the model if the model has
             * already been linked. The `update` action provides the means for
             * updating relationship data for the association.
             *
             * @param {Data.Bean} model
             * @chainable
             */
            linkRecord: function(model) {
                model.set('_action', this.isDefault(model) ? 'update' : 'create');
                this.add(model, {merge: true});

                return this;
            },

            /**
             * Adds a model to be unlinked.
             *
             * An `_action` attribute will be set to `delete` on the model if
             * the model has already been linked. The model is removed from the
             * collection if the model has not been linked.
             *
             * @param {Data.Bean} model
             * @chainable
             */
            unlinkRecord: function(model) {
                if (this.isDefault(model)) {
                    model.set('_action', 'delete');
                    this.add(model, {merge: true});
                } else {
                    this.undo(model);
                }

                return this;
            },

            /**
             * Removes a model so that it is neither linked or unlinked.
             *
             * @param {Data.Bean} model
             * @chainable
             */
            undo: function(model) {
                this.remove(model);

                return this;
            },

            /**
             * Stores the ID's of the models know to be related through this
             * relationship.
             *
             * There may be more that can be found through pagination.
             *
             * Models that were set to be linked or unlinked are removed from
             * the collection.
             *
             * @param {Array} [models] Array of model ID's. If empty, then the
             * ID's of the models currently in the collection will be used.
             */
            setDefaults: function(models) {
                var undos = [];

                this.defaults = _.isArray(models) ? models.slice() : this.pluck('id');

                this.each(function(model) {
                    if (_.contains(this.defaults, model.id)) {
                        undos.push(model);
                    }
                }, this);

                _.each(undos, this.undo, this);
            },

            /**
             * Clears the changes to the link.
             *
             * Models that were linked are added to the default set. Models
             * that were unlinked are removed from the default set.
             */
            clearAndUpdateDefaults: function() {
                var linked, unlinked;

                linked = _.union(this.where({'_action': 'create'}), this.where({'_action': 'update'}));
                unlinked = this.where({'_action': 'delete'});

                this.setDefaults(_.union(this.defaults, _.pluck(linked, 'id')));
                this.setDefaults(_.difference(this.defaults, _.pluck(unlinked, 'id')));
            }
        }, false);

        /**
         * @class VirtualCollection
         * @extends Data.MixedBeanCollection
         *
         * VirtualCollection manages changes to a field with the type
         * `collection`.
         *
         * New models can be {@link VirtualCollection#add linked} and existing
         * models can be {@link VirtualCollection#remove unlinked} when the
         * record is synchronized with the server.
         */
        VirtualCollection = app.MixedBeanCollection.extend({
            /**
             * @inheritdoc
             *
             * The initial set of models is assumed to be the state of the
             * collection on the server and do not need to be linked or
             * unlinked. Each {@link Link} instance is reset at the end of
             * construction to avoid marking the initial models to be linked or
             * unlinked. These defaults are stored for reference.
             *
             * To force all models to be linked during synchronization, create the
             * collection without models and subsequently add all models.
             */
            constructor: function(models, options) {
                app.MixedBeanCollection.prototype.constructor.call(this, models, options);

                _.each(this.links, function(link) {
                    // don't want change actions for the initial set
                    link.setDefaults();
                }, this);
            },

            /**
             * @inheritdoc
             *
             * {@link Link} instances are instantiated for each relationship
             * managed by the collection. The changes in each {@link Link}
             * instance are cleared when the collection is synchronized (See
             * {@link Link#clearAndUpdateDefaults}).
             *
             * @param {Data.Bean} options.parent The model to which this
             * collection is attached.
             * @param {String} options.fieldName The name of the attribute on
             * the parent model where this collection is stored.
             * @param {Array} options.links The link field names included for
             * this collection.
             */
            initialize: function(models, options) {
                options || (options = {});

                app.MixedBeanCollection.prototype.initialize.call(this, models, options);

                this.parent = options.parent;
                this.fieldName = options.fieldName;
                this.relatedModules = {};
                this.links = _.reduce(options.links, function(memo, link) {
                    var module, options;

                    module = app.data.getRelatedModule(this.parent.module, link);
                    this.relatedModules[module] = link;

                    options = {
                        link: {name: link, bean: this.parent},
                        module: module
                    };
                    memo[link] = new Link([], options);

                    return memo;
                }, {}, this);

                this.parent.on('sync', function() {
                    _.each(this.links, function(link) {
                        link.clearAndUpdateDefaults();
                    });
                }, this);
            },

            /**
             * @inheritdoc
             *
             * Determines which relationship the model can be linked to or
             * unlinked from and adds the reference to the model.
             */
            _prepareModel: function(model, options) {
                model = app.MixedBeanCollection.prototype._prepareModel.call(this, model, options);
                model.link = this.links[this.relatedModules[model.module]].link;

                return model;
            },

            /**
             * @inheritdoc
             *
             * Models that are marked to be unlinked and are found in the collection
             * on the server will not be unlinked when the collection is
             * synchronized.
             *
             * Models that are not found in the collection on the server will be
             * linked when the collection is synchronized.
             *
             * @fires See {@link VirtualCollection#_triggerChange}.
             * @chainable
             */
            add: function(models, options) {
                var added = [];

                options || (options = {});
                models = _.isArray(models) ? models.slice() : [models];

                if (_.compact(models).length === 0) {
                    return this;
                }

                _.each(models, function(model) {
                    var existingModel, relationship;

                    model = this._prepareModel(model, options);
                    existingModel = this.get(model.id);
                    relationship = this.links[model.link.name];

                    if (existingModel) {
                        if (options.merge) {
                            // set up an instruction for updating the
                            // relationship
                            relationship.linkRecord(model);
                        }
                    } else {
                        if (relationship.isDefault(model)) {
                            // reset the model in the relationship as there is
                            // no change
                            relationship.undo(model);
                        } else {
                            // set up an instruction for creating the
                            // relationship
                            relationship.linkRecord(model);
                        }
                    }

                    if (!existingModel || options.merge) {
                        app.MixedBeanCollection.prototype.add.call(this, model, options);
                        added.push(this.get(model.id));
                    }
                }, this);

                if (!options.silent && added.length > 0) {
                    this._triggerChange(added, options);
                }

                return this;
            },

            /**
             * @inheritdoc
             *
             * Models that are found in the collection on the server will be
             * unlinked when the collection is synchronized.
             *
             * Models that are not found in the collection on the server are simply
             * removed.
             *
             * @fires See {@link VirtualCollection#_triggerChange}.
             * @chainable
             */
            remove: function(models, options) {
                var removed = [];

                options || (options = {});
                models = _.isArray(models) ? models.slice() : [models];

                if (_.compact(models).length === 0) {
                    return this;
                }

                _.each(models, function(model) {
                    var existingModel, relationship;

                    existingModel = this.get(model);

                    if (existingModel) {
                        relationship = this.links[existingModel.link.name];
                        relationship.unlinkRecord(existingModel);
                        app.MixedBeanCollection.prototype.remove.call(this, existingModel, options);
                        removed.push(existingModel);
                    }
                }, this);

                if (!options.silent && removed.length > 0) {
                    this._triggerChange(removed, options);
                }

                return this;
            },

            /**
             * @inheritdoc
             *
             * Models that are found in both the collection on the server and
             * the new set of models will not be marked to be linked.
             *
             * Models that are found in the collection on the server but not in
             * the new set of models will be marked to be unlinked.
             *
             * TODO: The new models that are not defaults should be marked to
             * be linked. This will require a refactor where reset is called by
             * revert, instead of the other way around, and will impact
             * initialization with the default models.
             *
             * @fires See {@link VirtualCollection#_triggerChange}.
             * @chainable
             */
            reset: function(models, options) {
                var existingModels;

                options || (options = {});
                models = _.isArray(models) ? models.slice() : [models];

                this.revert(_.extend({}, options, {silent: true}));

                // take a snapshot of the original models
                existingModels = this.models.slice();

                app.MixedBeanCollection.prototype.reset.call(this, models, options);

                _.each(existingModels, function(existingModel) {
                    var relationship = this.links[existingModel.link.name];

                    /**
                     * Returns `true` if the new model exists in both the
                     * synchronized collection and the new collection; `false`
                     * if not.
                     *
                     * @param {Data.Bean} newModel
                     * @return {boolean}
                     */
                    function match(newModel) {
                        return (newModel.id === existingModel.id && newModel.module === existingModel.module);
                    }

                    // models that exist in both the synchronized collection
                    // and the new collection do not need to be linked
                    relationship.undo(existingModel);

                    if (!this.find(match)) {
                        // models from the synchronized collection, but not in
                        // the new collection should be unlinked
                        relationship.unlinkRecord(existingModel);
                    }
                }, this);

                if (!options.silent) {
                    this._triggerChange(this.models, options);
                }

                return this;
            },

            /**
             * Undo any changes to the collection since it was last synchronized.
             *
             * @fires See {@link VirtualCollection#_triggerChange}.
             * @fires reset Revert is a kind of reset, so it triggers a reset
             * event.
             * @param {Object} [options] See {@link Data.Bean#revertAttributes} for
             * usage patterns.
             * @chainable
             */
            revert: function(options) {
                var add, remove;

                options || (options = {});
                add = [];
                remove = [];

                // don't make changes to the collection until all changes have
                // been determined; otherwise the changes to the collection
                // will cause the iteration through models in each relationship
                // to be thrown off
                _.each(this.links, function(relationship) {
                    relationship.each(function(model) {
                        if (relationship.isDefault(model)) {
                            add.push(model);
                        } else {
                            remove.push(model);
                        }
                    });
                });

                this.remove(remove, {silent: true});
                this.add(add, {merge: true, silent: true});

                if (!options.silent) {
                    this._triggerChange(this.models, options);
                    this.trigger('reset', this, options);
                }

                return this;
            },

            /**
             * Returns `true` if the collection has changed; `false` if not.
             *
             * @return {boolean}
             */
            hasChanged: function() {
                var changed = false;

                _.each(this.links, function(link) {
                    if (link.length > 0) {
                        changed = true;
                    }
                }, this);

                return changed;
            },

            /**
             * Searches for records found within this collection's modules.
             *
             * @param {Object} [options] See {@link Data.DataManager#sync} for
             * usage patterns.
             * @return {SUGAR.HttpRequest}
             */
            search: function(options) {
                var callbacks, params, url;

                params = {};
                options || (options = {});

                params.q = options.query;

                // TODO: Invitee Search will return 30 for now, but leaving this in here
                // for when we move to using Unified Search which supports max_num
                params.max_num = options.limit;
                params.search_fields = options.search_fields? options.search_fields.join(',') : 'name';
                params.fields = options.fields ? options.fields.join(',') : 'name';

                if (this.links) {
                    params.module_list = _.map(this.links, function(link) {
                        return link.module;
                    }).join(',');
                }

                callbacks = {
                    success: function(data, request) {
                        if (options.success) {
                            options.success(app.data.createMixedBeanCollection(data.records), request);
                        }
                    },
                    error: function(e) {
                        if (options.error) {
                            options.error(e);
                        }
                    },
                    complete: function(request) {
                        if (options.complete) {
                            options.complete(request);
                        }
                    }
                };

                url = app.api.buildURL(this.parent.module, 'invitee_search', null, params);
                return app.api.call('read', url, null, callbacks);
            },

            /**
             * Triggers the changes on the {@link Data.Bean parent model}.
             *
             * Mimics the behavior found in {Backbone.Model#set} when an attribute
             * is changed.
             *
             * @fires change:field_name
             * @fires change
             * @param {*} change The relevant changes to the collection.
             * @param {Object} [options] See {@link Backbone.Model#trigger}.
             * @private
             */
            _triggerChange: function(change, options) {
                this.parent.trigger('change:' + this.fieldName, this.parent, this, change, options);
                this.parent.trigger('change', this, options);
            }
        });

        /**
         * @class BeanOverrides
         *
         * Exposes methods that are generically mixed into {@link Data.Bean} so
         * the plugin does not override model methods in an unsafe manner.
         *
         * @param {Data.Bean} model The overridden model can be used within the
         * mixins.
         * @constructor
         */
        BeanOverrides = function(model) {
            this.model = model;
        };

        /**
         * @see Data.Bean#toJSON
         *
         * {@link Data.Bean Beans} to be linked or unlinked via the link fields
         * will be reduced to a specific set of attributes.
         *
         *     @example
         *     {
         *         //...
         *         "contacts":{
         *             "add":[1,2],
         *             "delete":[3]
         *         }
         *         //...
         *     }
         */
        BeanOverrides.prototype.toJSON = function(options) {
            return _.reduce(this.model.getCollectionFieldNames(), function(json, attribute) {
                var field = this.get(attribute) || {};

                _.each(field.links, function(link, name) {
                    var actions = link.transpose();

                    if (actions.add || actions.delete) {
                        json[name] = actions;
                    }
                });

                return json;
            }, {}, this.model);
        };

        /**
         * @see Data.Bean#copy
         *
         * Copies any collection fields on the model from the source
         * {@link Data.Bean}.
         */
        BeanOverrides.prototype.copy = function(source, fields, options) {
            var attrs, clone, vardefs;

            attrs = {};
            vardefs = app.metadata.getModule(this.model.module).fields;

            /**
             * Removes the `_action` attribute from a model when copying it.
             *
             * @param {Data.Bean} model The model to copy to the collection
             * field of the target.
             * @return {Object} Attributes hash for the model.
             */
            clone = function(model) {
                var attributes = _.clone(model.attributes);

                delete attributes._action;

                return attributes;
            };

            _.each(fields, function(name) {
                var def = vardefs[name];

                if (def &&
                    def.duplicate_on_record_copy !== 'no' &&
                    (def.duplicate_on_record_copy === 'always' || !def.auto_increment) &&
                    source.has(name)
                ) {
                    attrs[name] = source.get(name).map(clone);
                }
            }, this.model);

            if (_.size(attrs) > 0) {
                this.model.set(attrs, options);
            }
        };

        /**
         * @see Data.Bean#set
         *
         * Creates a new {@link VirtualCollection} at the attribute using the
         * existing value as the default set of models. The default value of
         * the attribute is set to the collection to avoid triggering any
         * warnings due to the attribute changing.
         */
        BeanOverrides.prototype.set = function(attr, options) {
            _.each(attr, function(models, key) {
                var collection = new VirtualCollection(models, _.extend({}, options, {
                    parent: this,
                    fieldName: key,
                    links: this.fields[key].links
                }));
                this.attributes[key] = collection;
                this.setDefaultAttribute(key, collection);
            }, this.model);

            return this.model;
        };

        /**
         * @see Data.Bean#hasChanged
         *
         * Tests the collection fields when determining whether or not the
         * {@link Data.Bean} has changed.
         */
        BeanOverrides.prototype.hasChanged = function(attr) {
            var changed = false;

            if (attr == null || _.contains(this.model.getCollectionFieldNames(), attr)) {
                changed = this.model.get(attr).hasChanged();
            }

            return changed;
        };

        /**
         * @see Data.Bean#changedAttributes
         *
         * Includes in the return value any collection fields with collections
         * that have changed. When comparing objects, Backbone does not do a
         * deep comparison. As collections are objects, it is necessary to
         * perform this check ourselves.
         */
        BeanOverrides.prototype.changedAttributes = function(diff) {
            var changed = {};

            _.each(this.model.getCollectionFieldNames(), function(attr) {
                var collection = this.get(attr);

                if (collection.hasChanged()) {
                    changed[attr] = collection;
                }
            }, this.model);

            return changed;
        };

        /**
         * @see Data.Bean#revertAttributes
         *
         * Reverts all collections to their state when they were last
         * synchronized.
         */
        BeanOverrides.prototype.revertAttributes = function(options) {
            _.each(this.model.getCollectionFieldNames(), function(attr) {
                this.get(attr).revert(options);
            }, this.model);
        };

        /**
         * @see Data.Bean#getSyncedAttributes
         *
         * Includes in the return value all collection fields and their
         * associated link attributes. When comparing objects, Backbone does
         * not do a deep comparison. As collections are objects, the current
         * state of the collection is assumed to be synchronized. This method
         * handles the deep comparison for us.
         *
         * TODO: Don't assume the collection is synchronized when moving
         * collection field support to sidecar.
         */
        BeanOverrides.prototype.getSyncedAttributes = function() {
            var syncedAttributes = {};

            _.reduce(this.model.getCollectionFieldNames(), function(attributes, field) {
                attributes[field] = this.get(field);
            }, syncedAttributes, this.model);

            return syncedAttributes;
        };

        /**
         * The VirtualCollection plugin allows collections, made up of one or
         * more {@link Data.Bean} types, to be managed directly through an
         * attribute on a model and to synchronize changes to the associated
         * relationships at the same time as the model is synchronized.
         */
        app.plugins.register('VirtualCollection', ['model'], {
            /**
             * Wraps {@link Data.Bean} methods with custom behaviors in support
             * of the plugin. These methods include:
             *
             * {@link Data.Bean#toJSON}
             * {@link Data.Bean#copy}
             * {@link Data.Bean#set}
             * {@link Data.Bean#hasChanged}
             * {@link Data.Bean#changedAttributes}
             * {@link Data.Bean#revertAttributes}
             * {@link Data.Bean#getSyncedAttributes}
             *
             * @param {Data.Bean} model The model to which the plugin is
             * attached.
             * @param {Object} plugin The instance of the plugin.
             */
            onAttach: function(model, plugin) {
                var overrides = new BeanOverrides(this);

                /**
                 * Appends the JSON for the link fields to the JSON for the rest
                 * of the model.
                 */
                this.toJSON = _.wrap(this.toJSON, function(_super, options) {
                    return _.extend(_super.call(this, options), overrides.toJSON(options));
                });

                /**
                 * Copies the collection fields along with the rest of the
                 * attributes.
                 *
                 * See {@link Data.Bean#copy} and {@link BeanOverrides#copy}.
                 */
                this.copy = _.wrap(this.copy, function(_super, source, fields, options) {
                    var attrs, collections, collectionFieldNames, vardefs;

                    vardefs = app.metadata.getModule(this.module).fields;
                    fields = fields || _.pluck(vardefs, 'name');
                    collectionFieldNames = this.getCollectionFieldNames();
                    collections = _.intersection(collectionFieldNames, fields);
                    attrs = _.difference(fields, collectionFieldNames);

                    overrides.copy(source, collections, options);
                    _super.call(this, source, attrs, options);
                });

                /**
                 * Isolates the collection fields from the rest of the
                 * attributes when setting data on the model. Calls
                 * {@link BeanOverrides#set} to handle the collection fields
                 * and {@link Data.Bean#set} to handle the others.
                 */
                this.set = _.wrap(this.set, function(_super, key, val, options) {
                    var attrs, collections;

                    if (key == null) {
                        return this;
                    }

                    if (typeof key === 'object') {
                        attrs = key;
                        options = val;
                    } else {
                        (attrs = {})[key] = val;
                    }

                    options || (options = {});

                    collections = _.pick(attrs, this.getCollectionFieldNames());
                    attrs = _.omit(attrs, _.keys(collections));

                    overrides.set(collections, options);

                    return _super.call(this, attrs, options);
                });

                /**
                 * Defers to {@link BeanOverrides#hasChanged} when the
                 * attribute is a collection field.
                 */
                this.hasChanged = _.wrap(this.hasChanged, function(_super, attr) {
                    return _super.call(this, attr) || overrides.hasChanged(attr);
                });

                /**
                 * See {@link Data.Bean#changedAttributes} and
                 * {@link BeanOverrides#changedAttributes}.
                 */
                this.changedAttributes = _.wrap(this.changedAttributes, function(_super, diff) {
                    var changed = _.extend(_super.call(this, diff) || {}, overrides.changedAttributes(diff));

                    return _.isEmpty(changed) ? false : changed;
                });

                /**
                 * See {@link Data.Bean#revertAttributes} and
                 * {@link BeanOverrides#revertAttributes}.
                 */
                this.revertAttributes = _.wrap(this.revertAttributes, function(_super, options) {
                    overrides.revertAttributes(options);
                    _super.call(this, options);
                });

                /**
                 * See {@link Data.Bean#getSyncedAttributes} and
                 * {@link BeanOverrides#getSyncedAttributes}.
                 */
                this.getSyncedAttributes = _.wrap(this.getSyncedAttributes, function(_super) {
                    return _.extend(app.utils.deepCopy(_super.call(this) || {}), overrides.getSyncedAttributes());
                });
            },

            /**
             * Returns an array of field names for fields of type `collection`.
             *
             * @return {Array}
             */
            getCollectionFieldNames: function() {
                return _.chain(this.fields).where({type: 'collection'}).pluck('name').value();
            }
        });
    });
})(SUGAR.App);
