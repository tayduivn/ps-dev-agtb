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

/**
 * This plugin handles a collection (called the mass collection) of models.
 * It creates the mass collection in the context of the view it's attached to
 * and then provide convenient methods to `add` and `remove` models.
 *
 * The way to use it is to trigger the following context events:
 *  -`mass_collection:add` To add the model passed in argument to the mass
 *    collection.
 *  -`mass_collection:add:all` To add all models of the collection in the mass
 *     collection.
 *  -`mass_collection:remove` To remove the model passed in arguments from the
 *    mass collection.
 *  -`mass_collection:remove:all` To remove all models in the collection from
 *    the mass collection.
 */
(function(app) {
    app.events.on('app:init', function() {
        app.plugins.register('MassCollection', ['view'], {
            onAttach: function() {
                this.on('init', this._initMassCollectionPlugin, this);
                this.on('render', this._onMassCollectionRender, this);
            },

            /**
             * Initializes the plugin.
             *
             * @private
             */
            _initMassCollectionPlugin: function() {
                this.createMassCollection();
                this._preselectModels();
                this._bindMassCollectionEvents();
            },

            /**
             * Callback on view `render` that triggers an `all:check` event if
             * all records in the collection are checked.
             *
             * @private
             */
            _onMassCollectionRender: function() {
                if (this.collection.length !== 0) {
                    if (this._isAllChecked(this.massCollection)) {
                        this.massCollection.trigger('all:checked');
                    }
                }
            },

            /**
             * Creates the mass collection and set it in the context.
             *
             * @return {Data.BeanCollection} massCollection The mass collection.
             */
            createMassCollection: function() {
                this.massCollection = this.context.get('mass_collection');
                if (!this.massCollection) {
                    var MassCollection = app.BeanCollection.extend({
                        reset: function(models, options) {
                            this.filterDef = null;
                            this.entire = false;
                            Backbone.Collection.prototype.reset.call(this, models, options);
                        }
                    });
                    this.massCollection = new MassCollection();
                    this.context.set('mass_collection', this.massCollection);

                    // Resets the mass collection on collection reset for non
                    // independent mass collection.
                    if (!this.independentMassCollection) {
                        this.collection.on('reset', function() {
                            this.massCollection.reset();
                        }, this);
                    }
                }

                return this.massCollection;
            },

            /**
             * Adds preselected model to the mass collection.
             *
             * Because we only have a list of ids, and in order to display the
             * selected records we need the names, we have to fetch the names.
             *
             * @private
             */
            _preselectModels: function() {
                this.preselectedModelIds = this.context.get('preselectedModelIds');
                if (!_.isArray(this.preselectedModelIds)) {
                    this.preselectedModelIds = [this.preselectedModelIds];
                }
                if (_.isEmpty(this.preselectedModelIds)) {
                    return;
                }

                var preselectedCollection = app.data.createBeanCollection(this.module);
                preselectedCollection.fetch({
                    fields: ['name'],
                    params: {
                        filter: [
                            {'id': {'$in': this.preselectedModelIds}}
                        ]
                    },
                    success: _.bind(function(collection) {
                        this.addModel(collection.models);
                    }, this)
                });
            },

            /**
             * Binds mass collection events listeners.
             *
             * @private
             */
            _bindMassCollectionEvents: function() {
                this.context.on('mass_collection:add', this.addModel, this);
                this.context.on('mass_collection:add:all', this.addAllModels, this);
                this.context.on('mass_collection:remove', this.removeModel, this);
                this.context.on('mass_collection:remove:all', this.removeAllModels, this);
                this.context.on('mass_collection:clear', this.clearMassCollection, this);
            },

            /**
             * Adds a model or a list of models to the mass collection.
             *
             * @param {Data.Bean|Array} models The model or the list of models
             *   to add.
             */
            addModel: function(models) {
                models = _.isArray(models) ? models : [models];
                this.massCollection.add(models);
                if (this._isAllChecked(this.massCollection)) {
                    this.massCollection.trigger('all:checked');
                }
            },

            /**
             * Adds all models of the view collection to the mass collection.
             */
            addAllModels: function() {
                if (!this.independentMassCollection) {
                    this.massCollection.reset(this.collection.models);
                } else {
                    this.massCollection.add(this.collection.models);
                }
                this.massCollection.trigger('all:checked');
            },

            /**
             * Removes a model or a list of models from the mass collection.
             *
             * @param {Data.Bean|Array} models The model or the list of models
             *   to remove.
             */
            removeModel: function(models) {
                models = _.isArray(models) ? models : [models];
                this.massCollection.remove(models);
                if (!this._isAllChecked(this.massCollection)) {
                    this.massCollection.trigger('not:all:checked');
                }
            },

            /**
             * Removes all models of the view collection from the mass
             * collection.
             */
            removeAllModels: function() {
                if (!this.independentMassCollection) {
                    this.clearMassCollection(this.massCollection);
                } else {
                    this.massCollection.remove(this.collection.models);
                    this.massCollection.trigger('not:all:checked');
                }
            },

            /**
             * Clears the mass collection.
             */
            clearMassCollection: function() {
                this.massCollection.reset();
                this.massCollection.trigger('not:all:checked');
            },

            /**
             * Checks if all models of the view collection are in the mass
             * collection.
             *
             * @return {boolean} allChecked `true` if all models of the view
             *   collection are in the mass collection.
             * @private
             *
             */
            _isAllChecked: function() {
                if (this.massCollection.length < this.collection.length) {
                    return false;
                }
                var allChecked = _.every(this.collection.models, function(model) {
                    return this.massCollection.get(model.id);
                }, this);

                return allChecked;
            },

            /**
             * Unbind events on dispose.
             */
            onDetach: function() {
                $(window).off('resize.' + this.cid);
                this.context.off('mass_collection:add', null, this);
                this.context.off('mass_collection:add:all', null, this);
                this.context.off('mass_collection:remove', null, this);
                this.context.off('mass_collection:remove:all', null, this);
            }
        });
    });
})(SUGAR.App);
