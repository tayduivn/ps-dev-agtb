/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
(function(app) {
    app.events.on('app:init', function() {
        /**
         * Returns the link's JSON payload.
         *
         * @param {NestedLink} link
         * @return {Object}
         * @return {Array} return.create The JSON of the models that are to be
         * created and linked to the bean.
         * @return {Array} return.add The JSON of the models that are to be
         * linked to the bean.
         * @return {Array} return.delete An array of string ID's for the models
         * that are to be unlinked from the bean.
         */
        function toJSON(link) {
            var data = link.getData();

            if (data.create.length < 1) {
                delete data.create;
            }

            if (data.add.length < 1) {
                delete data.add;
            }

            if (data.delete.length > 0) {
                data.delete = _.pluck(data.delete, 'id');
            } else {
                delete data.delete;
            }

            return data;
        }

        /**
         * Returns the link field names for the links that are not being
         * managed by a {@link VirtualCollection} instance.
         *
         * This is a hack that avoids conflicts with {@link VirtualCollection}
         * until that plugin has been merged into this one. Otherwise, this
         * plugin would attempt to manage link fields that have been set up
         * for a {@link VirtualCollection} instance. This function can be
         * removed when the hack is no longer needed.
         *
         * @param {Data.Bean} model
         * @return {Array}
         */
        function getLinkFieldNames(model) {
            var collectionFieldNames = _.chain(model.fields).where({type: 'collection'}).pluck('name').value();
            var linkFieldNames = _.chain(model.fields).where({type: 'link'}).pluck('name').value();

            _.each(collectionFieldNames, function(fieldName) {
                var field = model.get(fieldName);

                if (_.isObject(field) && field.links) {
                    _.each(field.links, function(link) {
                        linkFieldNames = _.without(linkFieldNames, link.link.name);
                    });
                }
            }, model);

            return linkFieldNames;
        }

        /**
         * @class BeanOverrides
         *
         * {@link BeanOverrides} acts as a decorator for {@link Data.Bean}. It
         * exposes methods that are generically mixed into {@link Data.Bean} so
         * the plugin does not override model methods in an unsafe manner.
         *
         * @constructor
         * @param {Data.Bean} model The overridden model can be used within the
         * mixins at {@link BeanOverrides#model}.
         */
        var BeanOverrides = function(model) {
            this.model = model;
        };

        /**
         * {@link Data.Bean#toJSON}
         *
         * {@link Data.Bean Beans} to be linked or unlinked via the link fields
         * will be reduced to a specific set of attributes.
         *
         * @example
         * {
         *     //...
         *     "contacts":{
         *         "create":[{"name":"foo"}],
         *         "add":[1,2],
         *         "delete":[3]
         *     }
         *     //...
         * }
         */
        BeanOverrides.prototype.toJSON = function(links, options) {
            var json = {};

            _.each(_.unique(links), function(link) {
                var field = this.get(link);
                var actions;

                if (!field) {
                    return;
                }

                actions = toJSON(field);

                if (actions.create || actions.add || actions.delete) {
                    json[link] = actions;
                }
            }, this.model);

            return json;
        };

        /**
         * {@link Data.Bean#copy}
         *
         * Copies any link fields on the model from the source
         * {@link Data.Bean}.
         */
        BeanOverrides.prototype.copy = function(source, fields, options) {
            var attributes = {};
            var vardefs = app.metadata.getModule(this.model.module).fields;

            _.each(fields, function(name) {
                attributes[name] = [];
            });

            if (_.size(attributes) > 0) {
                // Create new collection for each link field.
                this.model.set(attributes, options);
            }

            _.each(fields, function(name) {
                var def = vardefs[name];

                if (def &&
                    def.duplicate_on_record_copy !== 'no' &&
                    (def.duplicate_on_record_copy === 'always' || !def.auto_increment) &&
                    source.has(name)
                ) {
                    // Copy data from source to the new collection.
                    this.get(name).add(source.get(name).toJSON());
                }
            }, this.model);
        };

        /**
         * {@link Data.Bean#set}
         *
         * Creates a {@link NestedLink} instance at the attribute using the
         * existing value as the default set of models. The default value of
         * the attribute is set to the collection to avoid triggering any
         * warnings due to the attribute changing.
         *
         * If `models` is an array, then that array is assumed to contain the
         * models to be inserted into the collection.
         *
         * If `models` is a {@link Backbone.Collection}, then the models from
         * that collection are copied and used in the new collection.
         *
         * If `models` is an object that came directly from the server, then it
         * should contain the keys `next_offset` and `records`, where `records`
         * is an array of models. These models are inserted into the collection.
         * `next_offset` is added to the `options` hash that is passed into the
         * {@link NestedLink} constructor.
         *
         * If `models` is not an array, is not a {@link Backbone.Collection},
         * is not null or undefined, and does not have a `records` key, then
         * it is assumed that the object represents a single model to be
         * inserted into the collection.
         *
         * Otherwise, `models` is nothing and the collection is initialized
         * without any models.
         */
        BeanOverrides.prototype.set = function(attr, options) {
            if (!this.model._changing) {
                // Attributes that have changed.
                this.model.changed = {};
            }

            _.each(attr, function(models, key) {
                var collection;
                var previous;
                var localOptions = _.extend({}, options, {
                    link: {
                        name: key,
                        bean: this
                    }
                });

                if (!_.isArray(models)) {
                    if (models instanceof Backbone.Collection) {
                        models = models.models;
                    } else if (models) {
                        if (models.next_offset) {
                            localOptions.next_offset = models.next_offset;
                        }

                        if (models.records) {
                            models = models.records;
                        } else {
                            models = [models];
                        }
                    } else {
                        models = [];
                    }
                }

                collection = new app.NestedLink(models, localOptions);

                previous = this.get(key);
                this.attributes[key] = collection;
                this.setDefault(key, collection);

                // Has the attribute changed since the last time it has been
                // set?
                if (!_.isEqual(app.utils.deepCopy(previous), app.utils.deepCopy(this.get(key)))) {
                    this.changed[key] = collection;

                    if (!localOptions.silent) {
                        this.trigger('change:' + key, collection);
                    }
                }
            }, this.model);

            return this.model;
        };

        /**
         * {@link Data.Bean#hasChanged}
         *
         * Tests the link fields fields when determining whether or not the
         * {@link Data.Bean bean} has changed.
         */
        BeanOverrides.prototype.hasChanged = function(attr) {
            if (attr == null) {
                // Test all link fields.
                attr = this.model.getNestedCollectionFieldNames('link');
            } else if (_.contains(this.model.getNestedCollectionFieldNames('link'), attr)) {
                // Only test one link field.
                attr = [attr];
            } else {
                // Don't test any link fields.
                attr = [];
            }

            return _.some(attr, function(attribute) {
                var collection = this.get(attribute);

                return (collection && collection.hasChanged());
            }, this.model);
        };

        /**
         * {@link Data.Bean#changedAttributes}
         *
         * Includes in the return value any link fields with collections that
         * have changed. When comparing objects, Backbone does not do a deep
         * comparison. As collections are objects, it is necessary to perform
         * this check ourselves.
         *
         * Comparing against `diff` isn't that important since it would require
         * comparing the models and internal collections of all link fields.
         * Normally this is used such that `diff` is the default attributes or
         * the synced attributes. The likelihood of a collection returning
         * `true` for `hasChanged` but then also matching `diff` exactly is
         * pretty slim. It seems safe to assume that if the collection has
         * changed then it won't match `diff`. And if the collection hasn't
         * changed then it probably will match `diff`.
         */
        BeanOverrides.prototype.changedAttributes = function(diff) {
            var changed = {};

            _.each(this.model.getNestedCollectionFieldNames('link'), function(attribute) {
                var collection = this.get(attribute);

                if (collection && collection.hasChanged()) {
                    changed[attribute] = collection;
                }
            }, this.model);

            return changed;
        };

        /**
         * {@link Data.Bean#getSynced}
         *
         * Includes in the return value all link fields. When comparing
         * objects, Backbone does not do a deep comparison. As collections are
         * objects, the current state of the collection is assumed to be
         * synchronized. This method handles the deep comparison for us.
         *
         * If a key is provided, only that attribute is returned.
         *
         * This behavior is an artifact of how
         * {@link Data.Bean#revertAttributes} works. If
         * `bean.changedAttributes(bean.getSynced())` returned a link field,
         * then {@link Data.Bean#revertAttributes} would make a deep copy of
         * the collection and set the value of the link attribute, which would
         * have the effect of creating a new NestedLink instance in its place.
         * That would cause the collection state to be lost, as well as the
         * ability to determine which models should exist in the collection
         * after it has been reverted. Since we know what
         * {@link Data.Bean#revertAttributes} intends to, we simply yield the
         * responsibility of reverting the collections to each collection
         * itself. If support for nested collections is added to Sidecar, then
         * {@link Data.Bean} can correctly handle this circumstance in
         * {@link Data.Bean#revertAttributes} by separating the collections
         * from the rest of the attributes before changing the value of the
         * attributes. This would mean that we don't have to assume that a
         * collection is synchronized anymore.
         */
        BeanOverrides.prototype.getSynced = function(key) {
            var syncedAttributes = {};

            if (key) {
                return this.model.get(key);
            }

            _.reduce(this.model.getNestedCollectionFieldNames('link'), function(memo, attribute) {
                var collection = this.get(attribute);

                if (collection) {
                    memo[attribute] = collection;
                }

                return memo;
            }, syncedAttributes, this.model);

            return syncedAttributes;
        };

        /**
         * {@link Data.Bean#revertAttributes}
         *
         * Reverts all links to their state when they were last synchronized.
         */
        BeanOverrides.prototype.revertAttributes = function(options) {
            _.each(this.model.getNestedCollectionFieldNames('link'), function(attribute) {
                var collection = this.get(attribute);

                if (collection) {
                    collection.revert(options);
                }
            }, this.model);
        };

        app.plugins.register('NestedCollection', ['model'], {
            onAttach: function(model, plugin) {
                var overrides = new BeanOverrides(this);

                /**
                 * Appends the JSON for the link fields to the JSON for the rest
                 * of the model.
                 */
                this.toJSON = _.wrap(this.toJSON, function(_super, options) {
                    var attributes;
                    var fields;
                    var links;
                    var linksToJSON;
                    var attrToJSON;
                    var linkFields = this.getNestedCollectionFieldNames('link');

                    fields = (options && options.fields) ? options.fields : _.keys(this.attributes);

                    // Get the JSON for all links.
                    links = _.intersection(linkFields, fields);
                    linksToJSON = overrides.toJSON(links, options);

                    // Get the JSON for all other attributes.
                    attributes = _.difference(fields, linkFields);
                    attrToJSON = _super.call(this, _.extend({}, options, {fields: attributes}));

                    return _.extend(attrToJSON, linksToJSON);
                });

                /**
                 * Copies the link fields along with the rest of the attributes.
                 *
                 * See {@link Data.Bean#copy} and {@link BeanOverrides#copy}.
                 */
                this.copy = _.wrap(this.copy, function(_super, source, fields, options) {
                    var attributes;
                    var links;
                    var linkFieldNames = this.getNestedCollectionFieldNames('link');
                    var vardefs = app.metadata.getModule(this.module).fields;

                    fields = fields || _.pluck(vardefs, 'name');
                    links = _.intersection(linkFieldNames, fields);
                    attributes = _.difference(fields, linkFieldNames);

                    overrides.copy(source, links, options);
                    _super.call(this, source, attributes, options);
                });

                /**
                 * Isolates the link fields from the rest of the attributes
                 * when setting data on the model. Calls
                 * {@link BeanOverrides#set} to handle the link fields and
                 * {@link Data.Bean#set} to handle the others.
                 */
                this.set = _.wrap(this.set, function(_super, key, val, options) {
                    var attributes;
                    var links;
                    var hasChanged;
                    var changedAttributes;

                    if (key == null) {
                        return this;
                    }

                    if (typeof key === 'object') {
                        attributes = key;
                        options = val;
                    } else {
                        (attributes = {})[key] = val;
                    }

                    options = options || {};

                    links = _.pick(attributes, this.getNestedCollectionFieldNames('link'));
                    attributes = _.omit(attributes, _.keys(links));

                    overrides.set(links, options);

                    // Any link attributes changed since the last set()?
                    changedAttributes = this.changed;

                    _super.call(this, attributes, options);

                    // If non-link attributes haven't changed but link
                    // attributes have, then fire the change event.
                    hasChanged = this.hasChanged();
                    _.extend(this.changed, changedAttributes);

                    if (!options.silent && !hasChanged && !_.isEmpty(changedAttributes)) {
                        this.trigger('change', this, options);
                    }

                    return this;
                });

                /**
                 * Defers to {@link BeanOverrides#hasChanged} when the
                 * attribute is a link field.
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
                 * See {@link Data.Bean#getSynced} and
                 * {@link BeanOverrides#getSynced}.
                 */
                this.getSynced = _.wrap(this.getSynced, function(_super, key) {
                    var fromOverrides = overrides.getSynced(key);
                    var fromSuper = _super.call(this, key);

                    if (key) {
                        // Let super return its value if the key isn't for a
                        // link.
                        return _.contains(this.getNestedCollectionFieldNames('link'), key) ? fromOverrides : fromSuper;
                    }

                    // Merge the link fields onto the object from super.
                    return _.extend(app.utils.deepCopy(fromSuper || {}), fromOverrides);
                });
            },

            /**
             * Returns an array of field names for fields of type `link` or
             * `collection`.
             *
             * @param {string} type Either `link` or `collection`.
             * @return {Array}
             */
            getNestedCollectionFieldNames: function(type) {
                if (type === 'link') {
                    return getLinkFieldNames(this);
                }

                return _.chain(this.fields).where({type: type}).pluck('name').value();
            }
        });
    });
})(SUGAR.App);
