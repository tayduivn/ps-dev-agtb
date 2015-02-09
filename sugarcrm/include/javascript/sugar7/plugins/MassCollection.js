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
                this._initTemplates();
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
                }
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
                if (_.isEmpty(this.preselectedModelIds)) {
                    return;
                }
                if (!_.isArray(this.preselectedModelIds)) {
                    this.preselectedModelIds = [this.preselectedModelIds];
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
                this.context.on('toggleSelectAllAlert', this.toggleSelectAllAlert, this);

                // Resets the mass collection on collection reset for non
                // independent mass collection.
                this.collection.on('reset', function() {
                    if (!this.independentMassCollection || this.massCollection.entire) {
                        this.massCollection.reset();
                    }
                }, this);

                this.collection.on('add', function() {
                    if (!this.disposed && !this._isAllChecked()) {
                        this.massCollection.trigger('not:all:checked');
                    }
                }, this);
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
             * Shows or hides the appropriate alert based on the state of the mass collection.
             *
             * FIXME: This method will be removed by SC-3999 because alerts
             * displayed within a list view are to be removed.
             */
            toggleSelectAllAlert: function() {
                var alert;
                if (!this.collection.length) {
                    return;
                }
                var selectedRecordsInPage = _.intersection(this.massCollection.models, this.collection.models);
                if (selectedRecordsInPage.length === this.collection.length) {
                    if (this.collection.next_offset > 0) {
                        alert = this._buildAlertForEntire();
                    } else {
                        alert = this._buildAlertForReset();
                    }
                } else if (this.massCollection.entire) {
                    alert = this._buildAlertForReset();
                }
                if (alert) {
                    this.layout.trigger('list:alert:show', alert);
                } else {
                    this.layout.trigger('list:alert:hide');
                }
            },



            /**
             * Builds the DOM alert with an event for resetting the mass collection.
             *
             * @param {number} [offset] The collection offset.
             * @return {jQuery} The alert content.
             * @protected
             *
             * FIXME: This method will be removed by SC-3999 because alerts
             * displayed within a list view are to be removed.
             */
            _buildAlertForReset: function(offset) {
                var self = this;
                var alert = $('<span></span>').append(this._selectedOffsetTpl({
                    offset: offset,
                    num: this.massCollection.length
                }));
                alert.find('[data-action=clear]').each(function() {
                    var $el = $(this);
                    $el.on('click', function() {
                        self.massCollection.reset();
                        self.massCollection.trigger('not:all:checked');
                        $el.off('click');
                    });
                    app.accessibility.run($el, 'click');
                });
                return alert;
            },

            /**
             * Builds the DOM alert with event for selecting all records.
             *
             * @return {jQuery} The alert content.
             * @protected
             *
             * FIXME: This method will be removed by SC-3999 because alerts
             * displayed within a list view are to be removed.
             */
            _buildAlertForEntire: function() {
                var self = this;
                var alert = $('<span></span>').append(this._selectAllTpl({
                        num: this.massCollection.length,
                        link: this._selectAllLinkTpl
                    }));
                alert.find('[data-action=select-all]').each(function() {
                    var $el = $(this);
                    $el.on('click', function() {
                        self.massCollection.entire = true;
                        self.getTotalRecords();
                        $el.off('click');
                    });
                    app.accessibility.run($el, 'click');
                });
                return alert;
            },

            /**
             * Fetch api to retrieve the entire filtered set.
             */
            getTotalRecords: function() {
                var filterDef = this.massCollection.filterDef || [];
                if (!_.isArray(filterDef)) {
                    filterDef = [filterDef];
                }
                //if list view is for linking and link fetch size configuration exists, set it,
                //otherwise default to maxRecordFetchSize
                var max_num = (this.meta.selection.isLinkAction && app.config.maxRecordLinkFetchSize) ?
                        app.config.maxRecordLinkFetchSize :
                        app.config.maxRecordFetchSize;
                var order = this.context.get('collection').orderBy;
                var fields = ['id'];

                // if any of the buttons require additional fields, add them to the list
                _.each(this.meta.selection.actions, function(button) {
                    if (_.isArray(button.related_fields)) {
                        fields = _.union(fields, button.related_fields);
                    }
                }, this);

                var params = {
                    fields: fields.join(','),
                    max_num: max_num
                };

                if (order && order.field) {
                    params.order_by = order.field + ':' + order.direction;
                }

                if (!_.isEmpty(filterDef)) {
                    params.filter = filterDef;
                }

                var url = app.api.buildURL(this.module, null, null, params);

                app.alert.show('totalrecord', {
                    level: 'process',
                    title: app.lang.get('LBL_LOADING'),
                    autoClose: false
                });

                this.massCollection.fetched = false;
                this.massCollection.trigger('massupdate:estimate');

                app.api.call('read', url, null, {
                    success: _.bind(function(data) {
                        if (this.disposed) {
                            return;
                        }
                        app.alert.dismiss('totalrecord');
                        this._processTotalRecords(data.records);
                        this._alertTotalRecords(data.next_offset);
                    }, this)
                });
            },

            /**
             * Update total record set from api request.
             *
             * @param {Object} collection The list of JSON formatted list of model ids.
             * @private
             */
            _processTotalRecords: function(collection) {
                this.massCollection.reset(collection);
                this.massCollection.entire = false;
                this.massCollection.fetched = true;
                this.massCollection.trigger('massupdate:estimate');
            },

            /**
             * Alert the message for total record set.
             *
             * @param {Number} offset Next pagination offset.
             * @private
             *
             * FIXME: This method will be removed by SC-3999 because alerts
             * displayed within a list view are to be removed.
             */
            _alertTotalRecords: function(offset) {
                var allSelected = this._buildAlertForReset(offset);
                this.layout.trigger('list:alert:show', allSelected);
            },


            /**
             * Initialize templates.
             *
             * @return {Field.ActionMenuField} Instance of this field.
             * @template
             * @protected
             *
             * FIXME: This method will be removed by SC-3999 because alerts
             * displayed within a list view are to be removed.
             */
            _initTemplates: function() {
                this._selectedOffsetTpl = app.template.getView('list.selected-offset') ||
                    app.template.getView('list.selected-offset', this.module);

                //FIXME: This should be move to a partial template when we are
                // going to move plugins to the clients folder.
                this._selectAllLinkTpl = new Handlebars.SafeString(
                    '<button type="button" class="btn btn-link btn-inline" data-action="select-all">' +
                        app.lang.get('LBL_LISTVIEW_SELECT_ALL_RECORDS') +
                        '</button>'
                );
                this._selectAllTpl = app.template.compile(null, app.lang.get('TPL_LISTVIEW_SELECT_ALL_RECORDS'));

                return this;
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
                this.context.off('toggleSelectAllAlert', null, this);
                this.context.off('mass_collection:clear', null, this);
            }
        });
    });
})(SUGAR.App);
