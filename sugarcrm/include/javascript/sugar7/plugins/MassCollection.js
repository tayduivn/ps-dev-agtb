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
 * This plugin handles the collection (called the mass collection)
 * of selected items in listViews.
 * It has to be attached to any view that has `actionmenu` fields.
 */
(function(app) {
    app.events.on('app:init', function() {
        app.plugins.register('MassCollection', ['view'], {
            onAttach: function() {
                this.on('init', function() {
                    this.createMassCollection();
                    this._preselectModels();
                    this.context.on('mass_collection:add', this.addModel, this);
                    this.context.on('mass_collection:add:all', this.addAllModels, this);
                    this.context.on('mass_collection:remove', this.removeModel, this);
                    this.context.on('mass_collection:remove:all', this.removeAllModels, this);
                    this.context.on('mass_collection:clear', this.clearMassCollection, this);

                    this.independentMassCollection = this.independentMassCollection ||
                        this.context.get('independentMassCollection') || false;

                }, this);

                this.on('render', function() {
                    var massCollection = this.context.get('mass_collection');
                    if (this.collection.length !== 0) {
                        if (this._isAllChecked(massCollection)) {
                            massCollection.trigger('all:checked');
                        }
                    }
                }, this);
            },

            /**
             * Creates the mass collection and set it in the context.
             *
             * @return {Collection} massCollection The mass collection.
             */
            createMassCollection: function() {
                var massCollection = this.context.get('mass_collection');
                if (!massCollection) {
                    var MassCollection = app.BeanCollection.extend({
                        reset: function(models, options) {
                            this.filterDef = null;
                            this.entire = false;
                            Backbone.Collection.prototype.reset.call(this, models, options);
                        }
                    });
                    massCollection = new MassCollection();
                    this.context.set('mass_collection', massCollection);

                    // Resets the mass collection on collection reset for non
                    // independent mass collection.
                    if (!this.independentMassCollection) {
                        this.collection.on('reset', function() {
                            massCollection.reset();
                        });
                    }
                }
                return massCollection;
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
             * Adds a model or a list of models to the mass collection.
             *
             * @param {Data.Bean|Array} models The model or the list of models
             *   to add.
             */
            addModel: function(models) {
                var massCollection = this.context.get('mass_collection');
                if (!massCollection) {
                    return;
                }

                models = _.isArray(models) ? models : [models];

                _.each(models, function(model) {
                    //each selection
                    if (model.id) {
                        massCollection.add(model);
                    }
                });

                if (this._isAllChecked(massCollection)) {
                    massCollection.trigger('all:checked');
                }
            },

            /**
             * Adds all models of the view collection to the mass collection.
             */
            addAllModels: function() {
                var massCollection = this.context.get('mass_collection');
                if (!massCollection) {
                    return;
                }
                if (!this.independentMassCollection) {
                    massCollection.reset(this.collection.models);
                } else {
                    massCollection.add(this.collection.models);
                }
                massCollection.trigger('all:checked');
            },

            /**
             * Removes a model or a list of models from the mass collection.
             *
             * @param {Data.Bean|Array} models The model or the list of models
             *   to remove.
             */
            removeModel: function(models) {
                var massCollection = this.context.get('mass_collection');
                if (!massCollection) {
                    return;
                }

                models = _.isArray(models) ? models : [models];

                _.each(models, function(model) {
                    if (model.id) {
                        massCollection.remove(model);
                    }
                });

                if (!this._isAllChecked(massCollection)) {
                    massCollection.trigger('not:all:checked');
                }
            },

            /**
             * Removes all models of the view collection from the mass
             * collection.
             */
            removeAllModels: function() {
                var massCollection = this.context.get('mass_collection');
                if (!massCollection) {
                    return;
                }
                if (!this.independentMassCollection) {
                    this.clearMassCollection(massCollection);
                } else {
                    massCollection.remove(this.collection.models);
                }
                massCollection.trigger('not:all:checked');
            },

            /**
             * Clears the mass collection.
             *
             * @param {Collection} [massCollection] The mass collection.
             */
            clearMassCollection: function(massCollection) {
                var massCollection = massCollection || this.context.get('mass_collection');
                if (!massCollection) {
                    return;
                }
                massCollection.reset();
                massCollection.trigger('not:all:checked');
            },

            /**
             * Checks if all models of the view collection are in the mass
             * collection.
             *
             * @return {boolean} allChecked `true` if all models of the view
             * collection are in the mass collection.
             */
            _isAllChecked: function(massCollection) {
                var allChecked = _.every(this.collection.models, function(model) {
                    return _.contains(_.pluck(massCollection.models, 'id'), model.id);
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
