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
    /**
     * @class NestedLink
     * @extends Data.BeanCollection
     * @alias SUGAR.App.NestedLink
     *
     * NestedLink manages changes to a field with the type `link`. New models
     * can be {@link NestedLink#add linked} and existing models can be
     * {@link NestedLink#remove unlinked} when the bean is synchronized with
     * the server. To achieve this, NestedLink is used in conjunction with the
     * NestedCollection plugin.
     */
    var NestedLink = app.BeanCollection.extend({
        /**
         * Contains the new models that are to be created and linked the next
         * time the bean is synchronized.
         * @type {Data.BeanCollection}
         * @instance
         * @private
         */
        _create: null,

        /**
         * Contains the existing models that are to be linked the next time the
         * bean is synchronized. Any attributes on the models will be assigned
         * to the respective columns on the join table, where applicable.
         * @type {Data.BeanCollection}
         * @instance
         * @private
         */
        _add: null,

        /**
         * Contains the existing models that are to be unlinked the next time
         * the bean is synchronized.
         * @type {Data.BeanCollection}
         * @instance
         * @private
         */
        _delete: null,

        /**
         * Contains the models that are already linked to the bean. Each time
         * the bean or collection is synchronized, this collection is updated
         * to reflect the changes.
         * @type {Data.BeanCollection}
         * @instance
         * @private
         */
        _synchronized: null,

        //TODO: The AddAsInvitee plugin can add models to a `VirtualCollection`
        // instance where the model is linked to the bean the next time the
        // bean is synchronized, but `VirtualCollection#hasChanged` still
        // reports `false`. `Link#hasChanged` shows how `Link#defaults` is used
        // to achieve this. This was in support of Meetings and Calls, where
        // models were being automatically added to the collection after it was
        // created. This had the effect of warning the user of unsaved changes
        // even when the user did not make any of those changes.
        // `Link#defaults` allowed us to filter out those models so that the
        // user wasn't warned. We will need the equivalent work-around when
        // `VirtualCollection` is refactored to use `NestedLink`. It might be
        // best for those modules to override any controllers where
        // `EditablePlugin#warnUnsavedChanges` might be called to prevent the
        // warning to be shown if certain logic is met.

        /**
         * @inheritdoc
         *
         * It is assumed, when the collection is created, that any models that
         * are not new are already linked to the bean on the server and do not
         * need to be linked again. Create an empty collection and subsequently
         * add models to the collection in order to force the models to be
         * linked to the bean the next time it is synchronized.
         *
         * @param {Object} options
         * @param {Object} options.link Mimics the options from
         * {@link Data.DataManager#createRelatedCollection}.
         * @param {Data.Bean} options.link.bean The bean into which this
         * collection is nested.
         * @param {string} options.link.name The name of the link, which is
         * also the name of the attribute under which this collection is
         * nested.
         * @param {number} [options.next_offset] When the initial models have
         * been retrieved from the server, use this option to define the next
         * offset that should be used when fetching the remaining records from
         * the server. If not provided, the next offset will be 0.
         */
        constructor: function(models, options) {
            var synchronized;
            var unsynchronized;

            options = options || {};
            app.BeanCollection.prototype.constructor.call(this, models, options);

            // Mimics the behavior from
            // `Data.DataManager#createRelatedCollection` to cache the
            // collection "in the given bean instance."
            this.setOption('relate', true);
            this.link.bean._setRelatedCollection(this.link.name, this);

            synchronized = this.filter(function(model) {
                return !model.isNew();
            });

            unsynchronized = this.filter(function(model) {
                return model.isNew();
            });

            // Mark the models that are already linked to the bean.
            this._synchronized.reset(synchronized);
            // Mark the models that need to be created and link the next time
            // the bean is synchronized.
            this._create.reset(unsynchronized);
            this._add.reset();
            this._delete.reset();

            // Mimics the behavior from
            // `Data.DataManager#getSyncSuccessCallback` for initializing the
            // properties related to pagination, with one exception. An
            // assumption is made that the offset was 0 when fetching the
            // initial models. Supplying `options.offset` will not shift the
            // offset.
            this.next_offset = options.next_offset || 0;
            // The initial offset is always the number of synchronized models.
            this.offset = this._synchronized.length;
            this.page = this.getPageNumber(options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(models, options) {
            // Define the properties used for inferring the type of models that
            // can be found in this collection.
            this.module = app.data.getRelatedModule(this.link.bean.module, this.link.name);
            this.model = app.data.getBeanClass(this.module);

            // Create the internal collections.
            this._create = app.data.createBeanCollection(this.module);
            this._add = app.data.createBeanCollection(this.module);
            this._delete = app.data.createBeanCollection(this.module);
            this._synchronized = app.data.createBeanCollection(this.module);

            app.BeanCollection.prototype.initialize.call(this, models, options);
        },

        /**
         * @inheritdoc
         */
        _bindCollectionEvents: function() {
            app.BeanCollection.prototype._bindCollectionEvents.call(this);

            // The internal collections are reset and the offset values are
            // adjusted when the bean is synchronized. `NestedLink#_create`,
            // `NestedLink#_add`, and `NestedLink#_delete` collections are
            // cleared. `NestedLink#_synchronized` is reset with the current
            // models in the collection.
            this.link.bean.on('sync', function() {
                var createCount = this._create.length;
                var unlinkCount = this._delete.length;

                // FIXME: The server does not respond with the records that
                // were created for the link. And keeping models without an id
                // around would cause trouble with resetting, reverting, or
                // paginating. After all, the collection believes all of its
                // models have been synchronized. So we discard the created
                // records and they can be fetched when the user paginates. If
                // the server did response with the records that were created,
                // then we would would still want to remove the models from
                // `NestedLink#_create` and replace them with the models the
                // server gives us.
                if (createCount > 0) {
                    this.remove(this._create.models, {silent: true});
                }

                this._create.reset();
                this._add.reset();
                this._delete.reset();
                this._synchronized.reset(this.models);

                if (this.next_offset === -1) {
                    // Before modifying the collection and synchronizing the
                    // bean, all related records had been fetched.
                    if (createCount > 0) {
                        // All related records had been fetched, but new ones
                        // were created. `NestedLink#offset` must be adjusted
                        // to allow the user to paginate and fetch the created
                        // records, along with any existing records that were
                        // linked, in whatever order is appropriate.
                        //
                        // The client should assume that unlinked records never
                        // existed, so `NestedLink#offset` is reduced by the
                        // number of unlinked records.
                        this.offset -= unlinkCount;

                        // It really only matters that `NestedLink#next_offset`
                        // is -1 or greater than 1. So it makes sense to keep
                        // `NestedLink#next_offset` aligned with
                        // `NestedLink#offset` when it is not to be -1.
                        this.next_offset = this.offset;
                    } else {
                        // All related records have been fetched.
                        // `NestedLink#offset` is the length of the collection
                        // and `NestedLink#next_offset` remains -1.
                        this.offset = this.length;
                    }
                } else {
                    // Not all related records have been fetched. The client
                    // should assume that unlinked records never existed, so
                    // the offset values are reduced by the number of unlinked
                    // records. This prevents the next pagination from skipping
                    // records that were on the next page prior to unlinking
                    // records from a previous page.
                    this.next_offset -= unlinkCount;
                    this.offset -= unlinkCount;
                }

                this.page = this.getPageNumber();
            }, this);

            this.on('sync', function(collection, models, options) {
                if (options.reset) {
                    // We've fetched a new set of records from the server
                    // to replace the existing ones. We need to reset all
                    // of the internal collections. This is typical of calling
                    // `NestedLink#fetch` instead of paging.
                    this._create.reset();
                    this._add.reset();
                    this._delete.reset();
                    this._synchronized.reset(this.models);
                } else {
                    // We've fetched more records from the server to add to
                    // the existing ones. We need to mark the new records
                    // as having been linked. This is most likely used when
                    // paging.
                    _.each(_.pluck(models, 'id'), function(id) {
                        var model = this.get(id);

                        if (model) {
                            // Models freshly fetched from the server are
                            // synchronized and can be removed from
                            // `NestedLink#_add`, as the models do not have any
                            // changes.
                            this._synchronized.add(model, {merge: true});
                            this._add.remove(model);
                        }
                    }, this);
                }
            }, this);

            // Triggers a `change` and `change:<attribute>` event on the bean
            // when changes are made to the collection. This allows for the
            // nested collection to behave like any other attribute on the
            // bean. The events will not be triggered if the silent option was
            // used to modify the collection.
            this.on('update reset', function(collection, options) {
                this.link.bean.trigger('change:' + this.link.name, this.link.bean, this, collection, options);
                this.link.bean.trigger('change', this.link.bean, options);
            }, this);
        },

        /**
         * @inheritdoc
         *
         * Adds any merged models -- that are already linked -- to
         * {@link NestedLink#_add} when the models have changed so that the
         * changes get synchronized the next time the bean is synchronized.
         */
        set: function(models, options) {
            models = app.BeanCollection.prototype.set.call(this, models, options);

            if (models) {
                _.each(_.isArray(models) ? models : [models], function(model) {
                    if (this._synchronized.get(model) && model.hasChanged()) {
                        this._add.add(model, options);
                    }
                }, this);
            }

            return models;
        },

        /**
         * @inheritdoc
         *
         * Adds the model to {@link NestedLink#_create} when the model is new
         * and to {@link NestedLink#_add} when the model is not new.
         */
        _addReference: function(model, options) {
            app.BeanCollection.prototype._addReference.call(this, model, options);

            if (model.isNew()) {
                this._create.add(model, options);
            } else {
                this._add.add(model, options);
            }
        },

        /**
         * @inheritdoc
         *
         * If the model is already linked, then it is added to
         * {@link NestedLink#_delete} to be unlinked when the bean is
         * synchronized.
         */
        _removeReference: function(model, options) {
            app.BeanCollection.prototype._removeReference.call(this, model, options);

            if (this._synchronized.get(model)) {
                this._delete.add(model, options);
            }

            // The model may have been set to be linked in a prior operation.
            // We no longer want it to be linked when the bean is synchronized.
            this._create.remove(model, options);
            this._add.remove(model, options);
        },

        /**
         * Returns the collection's JSON payload, which can be used for
         * synchronizing the changes to the link.
         *
         * @return {Object}
         * @return {Array} return.create The JSON of the models that are to be
         * created and linked to the bean.
         * @return {Array} return.add The JSON of the models that are to be
         * linked to the bean.
         * @return {Array} return.delete The JSON of the models that are to be
         * unlinked from the bean.
         */
        getData: function() {
            return {
                create: this._create.toJSON(),
                add: this._add.toJSON(),
                delete: this._delete.toJSON()
            };
        },

        /**
         * Returns the JSON of the models linked to the bean.
         *
         * @return {Array}
         */
        getSynced: function() {
            return this._synchronized.toJSON();
        },

        /**
         * Returns `true` if the collection has changed; `false` if not.
         *
         * @return {boolean}
         */
        hasChanged: function() {
            return !this._create.isEmpty() || !this._add.isEmpty() || !this._delete.isEmpty();
        },

        /**
         * @inheritdoc
         *
         * {@link NestedLink#_create}, {@link NestedLink#_add}, and
         * {@link NestedLink#_delete} are cleared. Any linked models that are
         * found in the collection before and after resetting it are added to
         * {@link NestedLink#_synchronized} because we know those are already
         * linked to the bean.
         *
         * {@link NestedLink#next_offset}, {@link NestedLink#offset}, and
         * {@link NestedLink#page} are not modified because reset is used by
         * {@link DataManager#sync}, which updates those properties based on
         * the API response. Modifying those properties again would cause
         * conflicts.
         */
        reset: function(models, options) {
            var previouslySynchronized = this._synchronized.models;

            this._create.reset();
            this._add.reset();
            this._delete.reset();
            this._synchronized.reset();
            models = app.BeanCollection.prototype.reset.call(this, models, options);

            _.each(previouslySynchronized, function(synchronized) {
                var model = this.get(synchronized);

                if (model) {
                    this._synchronized.add(model, {merge: true});

                    // No need to leave the model in `NestedLink#_add` if it
                    // has not changed since that would result in a noop when
                    // synchronizing the bean.
                    if (!model.hasChanged()) {
                        this._add.remove(model, options);
                    }
                }
            }, this);

            return models;
        },

        /**
         * Undo any changes to the collection since it was last synchronized.
         *
         * After calling this method, the only models in the collection are the
         * same that are in {@link NestedLink#_synchronized} and
         * {@link NestedLink#hasChanged} returns `false`.
         *
         * @param {Object} [options] See {@link Data.Bean#revertAttributes} for
         * usage patterns.
         */
        revert: function(options) {
            var models = this.reset(this._synchronized.models, options);

            // 0 when nothing is in `NestedLink#_synchronized`. N+1 when at
            // least one is in `NestedLink#_synchronized`.
            this.offset = this.next_offset = (this._synchronized.length || -1) + 1;
            this.page = this.getPageNumber(options);

            return models;
        },

        /**
         * @inheritdoc
         *
         * @param {boolean} [options.all] Will fetch all records when `true`.
         * There is no return value when `options.all` is used.
         */
        fetch: function(options) {
            var success;

            /**
             * Paginates the collection until all records have been fetched and
             * then calls `options.success`.
             */
            var paginate = _.bind(function() {
                if (this.next_offset > -1) {
                    this.paginate(options);
                } else if (success) {
                    success(this, options);
                }
            }, this);

            // Use the sorting options from the view defs.
            options = options || {};
            options.order_by = options.order_by || this.link.bean.fields[this.link.name].order_by;

            if (options.all) {
                delete options.all;

                // Increase the limit to reduce the number of requests.
                options.limit = _.max([options.limit, app.config.maxSubpanelResult, app.config.maxQueryResult]);

                if (options.success) {
                    success = options.success;
                    delete options.success;
                }

                options.success = paginate;
                paginate();
            } else {
                return app.BeanCollection.prototype.fetch.call(this, options);
            }
        },

        /**
         * @inheritdoc
         */
        paginate: function(options) {
            // Paging should always add to the collection.
            options = options || {};
            options.add = true;
            app.BeanCollection.prototype.paginate.call(this, options);
        }
    }, false);

    app.augment('NestedLink', NestedLink, false);
})(SUGAR.App);
