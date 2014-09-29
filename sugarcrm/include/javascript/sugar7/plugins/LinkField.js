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
        var getLinkUrl, LinkField, ModelOverrides;

        /**
         * Returns a relative URL to use for fetching related records or
         * linking or unlinking an existing record to or from another record.
         *
         *     @example
         *     ```
         *     /v10/Meetings/12345/link/
         *     ```
         *
         * Append the relationship name followed by the ID of related record to
         * either link or unlink a related record.
         *
         * @param {Bean} lhs
         * @return {string}
         */
        getLinkUrl = function(lhs) {
            return '/v10/' + lhs.module + '/' + lhs.id + '/link/';
        };

        /**
         * @class LinkField
         * @extends MixedBeanCollection
         *
         * Manages the state of a {@link BeanCollection} or
         * {@link MixedBeanCollection} so that it can be used as if it was any
         * other attribute on a {@link Bean}.
         *
         * A {@link Bean} can contain any number of collections. These
         * collections are synchronized asynchronously when the {@link Bean} is
         * synchronized.
         */
        LinkField = app.MixedBeanCollection.extend({
            /**
             * @property {Boolean} [isLoading=false]
             * TRUE when the collection is being fetched from the server
             */
            isLoading: false,

            /**
             * Sets all properties that are referenced by other methods.
             *
             * @param {Array} models Can be empty to initialize with no models
             * @param {Object} options See {@link MixedBeanCollection#initialize}
             * @param {Bean} options.parent The model to which this collection
             * is attached
             * @param {String} options.fieldName The name of the attribute on
             * the parent model where this collection is stored
             * @param {Array} options.links The link field names included for
             * this collection
             */
            initialize: function(models, options) {
                options || (options = {});

                app.MixedBeanCollection.prototype.initialize.call(this, models, options);

                this.bulkUrl = app.api.buildURL(null, 'bulk');

                this.parent = options.parent;
                this.fieldName = options.fieldName;
                this.links = app.metadata.getRHSModulesForLinks(this.parent.module, options.links);
            },

            /**
             * Adds one or more models to the collection.
             *
             * The delta attribute is set to 1 for models that are not found in
             * the collection on the server. These beans will be linked when
             * the collection is synced.
             *
             * The delta attribute is set to 0 for models that are found in the
             * collection on the server. This acts as a revert for a model that
             * was previously marked to be unlinked. These beans will be
             * ignored during synchronization since they result in a no-op.
             *
             * @fires change:field_name Triggered on the
             * {@link Bean parent model}
             * @fires change Trigger on the {@link Bean parent model}
             * @param {Array} [models] See {@link MixedBeanCollection#add}
             * @param {Object} [options] See {@link MixedBeanCollection#add}
             * @return {Array} See {@link MixedBeanCollection#add}
             */
            add: function(models, options) {
                var added = [];

                models = _.isArray(models) ? models.slice() : [models];

                if (models.length === 0) {
                    return this;
                }

                options || (options = {});

                _.each(models, function(model) {
                    var delta, existingModel;

                    model = this._prepareModel(model, options);
                    existingModel = this.get(model.id);

                    if (existingModel) {
                        if (existingModel.get('delta') === -1) {
                            existingModel.set('delta', 0);
                            app.MixedBeanCollection.prototype.add.call(
                                this,
                                existingModel,
                                _.extend({}, options, {merge: true})
                            );
                            added.push(this.get(existingModel.id));
                        }
                    } else {
                        delta = model.get('delta');
                        if (_.isEmpty(delta) && delta !== 0) {
                            model.set('delta', 1);
                        }
                        app.MixedBeanCollection.prototype.add.call(this, model, options);
                        added.push(this.get(model.id));
                    }
                }, this);

                if (!options.silent && added.length > 0) {
                    this.parent.trigger('change:' + this.fieldName, this.parent, this, added, options);
                    this.parent.trigger('change', this, options);
                }

                return this;
            },

            /**
             * Removes one or more models from the collection.
             *
             * The delta attribute is set to -1 for models found in the
             * collection on the server. These beans will be unlinked when the
             * collection is synced.
             *
             * Models that are not found in the collection on the server are
             * simply removed.
             *
             * @fires change:field_name Triggered on the
             * {@link Bean parent model}
             * @fires change Trigger on the {@link Bean parent model}
             * @param {Array} [models] See {@link MixedBeanCollection#remove}
             * @param {Object} [options] See {@link MixedBeanCollection#remove}
             * @return {Array} See {@link MixedBeanCollection#remove}
             */
            remove: function(models, options) {
                var removed = [];

                models = _.isArray(models) ? models.slice() : [models];

                if (models.length === 0) {
                    return this;
                }

                options || (options = {});

                _.each(models, function(model) {
                    var existingModel;

                    if (model instanceof Backbone.Model || (_.isObject(model) && !_.isEmpty(model.id))) {
                        model = model.id;
                    }

                    existingModel = this.get(model);

                    if (existingModel) {
                        removed.push(existingModel);

                        if (existingModel.get('delta') === 1) {
                            app.MixedBeanCollection.prototype.remove.call(this, model, options);
                        } else {
                            existingModel.set('delta', -1, options);
                        }
                    }
                }, this);

                if (!options.silent && removed.length > 0) {
                    this.parent.trigger('change:' + this.fieldName, this.parent, this, removed, options);
                    this.parent.trigger('change', this, options);
                }

                return this;
            },

            /**
             * Replace the collection's models with a new set.
             *
             * A delta attribute is set to 0 for all models that exist in the
             * collection on the server. It is set to 1 for all models that do
             * not exist in the collection on the server. The caller can force
             * the delta attribute to a particular value by setting it on the
             * models before they are passed in.
             *
             * @fires change:field_name Triggered on the
             * {@link Bean parent model}
             * @fires change Trigger on the {@link Bean parent model}
             * @param {Array} [models] See {@link MixedBeanCollection#reset}
             * @param {Object} [options] See {@link MixedBeanCollection#reset}
             * @return {Array} See {@link MixedBeanCollection#reset}
             */
            reset: function(models, options) {
                models = _.isArray(models) ? models.slice() : [models];

                options || (options = {});

                _.each(models, function(model) {
                    var delta, existingModel;

                    model = this._prepareModel(model, options);
                    delta = model.get('delta');

                    if (_.isUndefined(delta) || _.isNull(delta)) {
                        existingModel = this.get(model.id);

                        if (existingModel && existingModel.get('delta') < 1) {
                            model.set(delta, 0);
                        } else {
                            model.set(delta, 1);
                        }
                    }
                }, this);

                app.MixedBeanCollection.prototype.reset.call(this, models, options);

                if (!options.silent) {
                    this.parent.trigger('change:' + this.fieldName, this.parent, this, this.models, options);
                    this.parent.trigger('change', this, options);
                }

                return this;
            },

            /**
             * Provides a public save method, which simply calls
             * {@link LinkField#sync}, so that this class' interface mimics
             * that of a model.
             *
             * @param {Object} [options] See {@link LinkField#sync}
             * @return {Boolean} See {@link LinkField#sync}
             * @return {SUGAR.HttpRequest} See {@link LinkField#sync}
             */
            save: function(options) {
                return this.sync(options);
            },

            /**
             * Undo any changes to the collection since it was last synced.
             *
             * @param {Object} [options] See {@link Bean#revertAttributes} for
             * usage patterns
             * @return {Array} See {@link LinkField#reset}
             */
            revert: function(options) {
                var originals;

                options || (options = {});

                originals = this.filter(function(model) {
                    if (model.get('delta') < 1) {
                        model.set('delta', 0);
                        return true;
                    } else {
                        return false;
                    }
                });

                return this.reset(originals, options);
            },

            /**
             * Returns true if the collection has been changed since it was
             * last synchronized.
             *
             * The collection is considered dirty if the delta for any models
             * is -1 or 1.
             *
             * @return {Boolean}
             */
            isDirty: function() {
                return !!this.find(function(model) {
                    return model.get('delta') !== 0;
                });
            },

            /**
             * Reset the contents of the collection with its state from the
             * server.
             *
             * Uses the BulkApi to retrieve each single-typed collection and
             * reduces the separate collections into one multi-typed collection.
             *
             * @param {Object} [options] See {@link Bean#fetch} for usage
             * patterns
             * @param {Function} [options.success] On success callback
             * @param {Function} [options.error] On error callback
             * @param {Function} [options.complete] On complete callback
             * @return {SUGAR.HttpRequest} AJAX request
             */
            fetch: function(options) {
                var error, linkUrl, success, requests;

                this.isLoading = true;
                options || (options = {});
                linkUrl = getLinkUrl(this.parent);

                //TODO: going to need a custom endpoint to get the free-busy data along with the related beans
                //... you can pass it in via options.endpoint and follow the same logic as data-manager
                //... just extend the api to get the extra free-busy data for each person
                requests = _.chain(this.links)
                    .keys()
                    .map(function(linkName) {
                        return {url: linkUrl + linkName};
                    })
                    .value();

                success = options.success;
                options.success = _.bind(function(result) {
                    var records = [];

                    _.each(result, function(data) {
                        records = records.concat(data.contents.records.map(function(record) {
                            record.delta = 0;
                            return record;
                        }));
                    });

                    this.isLoading = false;

                    this.reset(records);

                    if (success) {
                        success(this, records);
                    }
                }, this);

                error = options.error;
                options.error = _.bind(function(e) {
                    this.isLoading = false;
                    if (error) {
                        //TODO: that callback should handle the error by flashing it or whatever
                        error(e);
                    }
                }, this);

                return app.api.call('create', this.bulkUrl, {requests: requests}, options);
            },

            /**
             * Save the changes to the collection on the server.
             *
             * @fires sync When the collection is successfully synchronized
             * @fires error When the collection fails to synchronize
             * @fires complete When the attempt to synchronize the collection
             * is completed
             * @param {Object} [options] See {@link Bean#sync} for usage
             * patterns
             * @param {Function} [options.success] On success callback
             * @param {Function} [options.error] On error callback
             * @param {Function} [options.complete] On complete callback
             * @return {Boolean} FALSE when there are no changes to sync
             * @return {SUGAR.HttpRequest} AJAX request
             */
            sync: function(options) {
                var complete, error, linksInverted, linkUrl, success, requests;

                options || (options = {});
                linkUrl = getLinkUrl(this.parent);

                linksInverted = _.invert(this.links);
                requests = this.filter(function(model) {
                    return model.get('delta') !== 0;
                }).map(function(model) {
                    return {
                        method: (model.get('delta') === 1) ? 'POST' : 'DELETE',
                        url: linkUrl + linksInverted[model.module] + '/' + model.id
                    };
                });

                if (requests.length === 0) {
                    return false;
                }

                success = _.bind(function(data, request) {
                    this.trigger('sync', this, data, options, request);
                }, this);

                error = _.bind(function(e) {
                    this.trigger('error', this, options, e);
                }, this);

                complete = _.bind(function(request) {
                    this.trigger('complete', this, options, request);
                }, this);

                return app.api.call(
                    'create',
                    this.bulkUrl,
                    {
                        requests: requests
                    },
                    {
                        success: success,
                        error: error,
                        complete: complete
                    }
                );
            },

            /**
             * Searches for records found within this collections' modules.
             *
             * @param {Object} [options] See {@link data#sync} for usage
             * patterns
             * @param {Function} [options.success] On success callback
             * @param {Function} [options.error] On error callback
             * @param {Function} [options.complete] On complete callback
             * @return {SUGAR.HttpRequest} AJAX request
             */
            search: function(options) {
                var url, callbacks, params;

                params = {};
                options || (options = {});

                params.q = options.query;
                // TODO: Invitee Search will return 30 for now, but leaving this in here
                // for when we move to using Unified Search which supports max_num
                params.max_num = options.limit;
                params.search_fields = 'first_name,last_name,email,account_name';
                params.fields = 'id,full_name,email,account_name';
                if (this.links) {
                    params.module_list = _.values(this.links).join(',');
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
            }
        });

        /**
         * @class ModelOverrides
         * Exposes methods that are generically mixed into {@link Bean} so the
         * plugin does not override model methods in an unsafe manner.
         *
         * @param {Bean} model The overridden model can be used within the
         * mixins.
         * @constructor
         */
        ModelOverrides = function(model) {
            this.model = model;
        };

        /**
         * Removes the reference to the collection at attr that will be unset
         * to allow for reinitializing it.
         *
         * @param {String} attr The key under which the collection is stored
         * @param {Object} [options] Not used, but retained so the method
         * signature matches {@link Backbone.Model#unset}
         */
        ModelOverrides.prototype.unset = function(attr, options) {
            if (this.model.linkFields && this.model.linkFields.length > 0) {
                this.model.linkFields = _.without(this.model.linkFields, attr);
            }
        };

        /**
         * Removes references to all collections that will be cleared to allow
         * for reinitializing them.
         *
         * @param {Object} [options] Not used, but retained so the method
         * signature matches {@link Backbone.Model#clear}
         */
        ModelOverrides.prototype.clear = function(options) {
            if (this.model.linkFields && this.model.linkFields.length > 0) {
                this.model.linkFields = [];
            }
        };

        /**
         * Wraps the success callback of {@link Bean#save} to allow for syncing
         * changes to the collections.
         *
         * The original success callback is called once all collections are
         * successfully synced.
         *
         * @fires sync Triggered on the {@link Bean model}
         * @fires data:sync:success Triggered on {@link data}
         *
         * The original error callback is called if any of the attempts fail to
         * sync a collection. This call is delayed until all collections are
         * synced so that the server can get the latest data even if one
         * attempt fails.
         *
         * @fires data:sync:error Triggered on the {@link Bean model} and
         * {@link data}
         *
         * The original complete callback is called once all attempts have been
         * made to sync the collections.
         *
         * @fires data:sync:complete Triggered on the {@link Bean model} and
         * {@link data}
         *
         * @param {Object} [attributes] See {@link Bean#save} for usage patterns
         * @param {Object} [options] See {@link Bean#save} for usage patterns
         * @param {Function} [options.success] On success callback
         * @param {Function} [options.error] On error callback
         * @param {Function} [options.complete] On complete callback
         */
        ModelOverrides.prototype.save = function(attributes, options) {
            var complete, error, success;

            options || (options = {});

            if (this.model.linkFields && this.model.linkFields.length > 0) {
                success = options.success;
                error = options.error;
                complete = options.complete;

                options.success = _.bind(function(model, data, options) {
                    var collections, finish;

                    collections = {
                        total: this.model.linkFields.length,
                        success: {},
                        error: {},
                        complete: {}
                    };

                    finish = function() {
                        var args, completed, errors, successes;

                        successes = _.size(collections.success);
                        errors = _.size(collections.errors);
                        completed = _.size(collections.complete);

                        if (successes === collections.total && completed < collections.total) {
                            args = _.last(_.toArray(collections.success));

                            if (success) {
                                success(args.model, args.data, args.options);
                            }

                            args.model.trigger('sync', args.model, args.data, args.options, args.request);
                            app.data.trigger('data:sync:success', 'create', args.model, args.options, args.request);
                        } else if (errors > 0 && (successes + errors) === collections.total) {
                            args = _.first(_.toArray(collections.error));

                            app.error.handleHttpError(args.error, args.model, args.options);
                            app.data.trigger('data:sync:error', 'create', args.model, args.options, args.error);
                            args.model.trigger('data:sync:error', 'create', args.options, args.error);

                            if (error) {
                                error(args.error);
                            }
                        } else if (completed === collections.total) {
                            args = _.last(_.toArray(collections.complete));

                            app.data.trigger('data:sync:complete', 'create', args.model, args.options, args.request);
                            args.model.trigger('data:sync:complete', 'create', args.options, args.request);

                            if (complete) {
                                complete(args.request);
                            }
                        }
                    };

                    _.each(this.model.linkFields, function(fieldName) {
                        var collection = this.get(fieldName);

                        collection.on('sync', function(result, request) {
                            data[fieldName] = result;
                            collections.success[fieldName] = {
                                model: model,
                                data: data,
                                options: options,
                                request: request
                            };
                            finish();
                        });

                        collection.on('error', function(coll, opts, e) {
                            collections.error[fieldName] = {error: e, model: model, options: options};
                            finish();
                        });

                        collection.on('complete', function(request) {
                            collections.complete[fieldName] = {model: model, options: options, request: request};
                            finish();
                        });

                        // save will fail if there is nothing to sync, so mimic the behavior of a successful sync
                        if (!collection.save()) {
                            collections.success[fieldName] = {
                                model: model,
                                data: data,
                                options: options,
                                request: null
                            };
                            finish();
                            collections.complete[fieldName] = {model: model, options: options, request: null};
                            finish();
                        }
                    }, this.model);
                }, this);
            }
        };

        /**
         * Includes the current state of each collection when getting synced
         * attributes in order to avoid setting off any alarms by pretending
         * the collection is always synced.
         *
         * The plugin will manually handle cases where the collections are
         * dirty to achieve the expected behavior when reverting and/or warning
         * about unsaved changes.
         *
         * @return {Object}
         */
        ModelOverrides.prototype.getSyncedAttributes = function() {
            var synced = {};

            if (this.model.linkFields && this.model.linkFields.length > 0) {
                _.each(this.model.linkFields, function(fieldName) {
                    synced[fieldName] = this.get(fieldName);
                }, this.model);
            }

            return synced;
        };

        /**
         * Reverts all collections to their state when they were last fetched.
         *
         * @param {Object} [options] See {@link Bean#revertAttributes} for
         * usage patterns
         */
        ModelOverrides.prototype.revertAttributes = function(options) {
            if (this.model.linkFields && this.model.linkFields.length > 0) {
                _.each(this.model.linkFields, function(fieldName) {
                    this.get(fieldName).revert(options);
                }, this.model);
            }
        };

        /**
         * Returns the collections that are dirty.
         *
         * A dirty collection is one where any model has a delta of either -1
         * or 1.
         *
         * @param {Object} [attributes] Not used, but retained so the method
         * signature matches {@link Backbone.Model#changedAttributes}
         * @return {Object}
         */
        ModelOverrides.prototype.changedAttributes = function(attributes) {
            var changed = {};

            if (this.model.linkFields && this.model.linkFields.length > 0) {
                _.each(this.model.linkFields, function(fieldName) {
                    var collection = this.get(fieldName);

                    if (collection.isDirty()) {
                        changed[fieldName] = collection;
                    }
                }, this.model);
            }

            return changed;
        };

        /**
         * Copies all link fields on the model along with other copy rules
         * TODO: When link fields are added to vardef, add check here
         * for whether duplicate_on_record_copy is set
         *
         * @param {Data.Bean} source The bean to copy the fields from.
         * @param {Array} [fields] The fields to copy. All fields are copied if not specified.
         * @param {Object} [options] Standard Backbone options that should be passed to `Backbone.Model#set` method.
         */
        ModelOverrides.prototype.copy = function(source, fields, options) {
            var linkFieldsToCopy = [];

            if (source.linkFields && source.linkFields.length > 0) {
                linkFieldsToCopy = source.linkFields;

                //restrict link fields that are copied if specific list is passed in
                if (fields && fields.length > 0) {
                    linkFieldsToCopy = _.intersection(linkFieldsToCopy, fields);
                }
            }

            _.each(linkFieldsToCopy, function(fieldName) {
                this.model.copyLinkField(source, fieldName);
            }, this);
        };

        app.plugins.register('LinkField', ['model'], {
            onAttach: function(model, plugin) {
                var overrides = new ModelOverrides(this);

                // override {@link Bean#unset}
                this.unset = _.wrap(this.unset, function(_super, attr, options) {
                    overrides.unset(attr, options);
                    return _super.call(this, attr, options);
                });

                // override {@link Bean#clear}
                this.clear = _.wrap(this.clear, function(_super, options) {
                    overrides.clear(options);
                    return _super.call(this, options);
                });

                // override {@link Bean#save}
                this.save = _.wrap(this.save, function(_super, attributes, options) {
                    overrides.save(attributes, options);
                    return _super.call(this, attributes, options);
                });

                // override {@link Bean#getSyncedAttributes}
                this.getSyncedAttributes = _.wrap(this.getSyncedAttributes, function(_super) {
                    return _.extend(_super.call(this), overrides.getSyncedAttributes());
                });

                // override {@link Bean#revertAttributes}
                this.revertAttributes = _.wrap(this.revertAttributes, function(_super, options) {
                    overrides.revertAttributes(options);
                    _super.call(this, options);
                });

                // override {@link Bean#changedAttributes}
                this.changedAttributes = _.wrap(this.changedAttributes, function(_super, attributes) {
                    var changed = _.extend(
                        _super.call(this, attributes) || {},
                        overrides.changedAttributes(attributes)
                    );
                    return _.isEmpty(changed) ? false : changed;
                });

                // override {@link Bean#copy}
                this.copy = _.wrap(this.copy, function(_super, source, fields, options) {
                    overrides.copy(source, fields, options);
                    _super.call(this, source, fields, options);
                });

                // keeps track of what attributes contain collections so they
                // can be maintained automatically by this plugin
                this.linkFields = [];

                // any JS code can trigger the initialization of particular
                // collection
                this.on('collection:initialize', function(fieldName, options) {
                    var collection;

                    // to avoid race conditions, the collection will only be
                    // initialized if it hasn't already been initialized
                    if (!_.contains(this.linkFields, fieldName)) {
                        collection = new LinkField([], {
                            parent: this,
                            fieldName: fieldName,
                            links: options.links
                        });

                        this.set(fieldName, collection);
                        this.linkFields.push(fieldName);

                        // set this attributes default to the new collection so
                        // that sidecar doesn't treat the existence of the
                        // collection as a changed attribute
                        this.setDefaultAttribute(fieldName, collection);
                    }
                }, this);
            },

            /**
             * Copy models from the source model's link field to current model's link field
             *
             * @param {Data.Bean} source The model to copy from
             * @param {String} fieldName name of the field on the source model to copy from
             */
            copyLinkField: function(source, fieldName) {
                var sourceCollection = source.get(fieldName) || {},
                    targetCollection,
                    links = sourceCollection.links;

                if (sourceCollection instanceof app.BeanCollection && links) {
                    this.trigger('collection:initialize', fieldName, {links: _.keys(links)});
                    targetCollection = this.get(fieldName);
                    sourceCollection.each(function(model) {
                        model = model.clone();
                        model.set('delta', 1); // mark as new
                        targetCollection.add(model);
                    });
                }
            }
        });
    });
})(SUGAR.App);
