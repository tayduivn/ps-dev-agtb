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
 *
 * @class View.Views.Base.MultiSelectionListLinkView
 * @alias SUGAR.App.view.views.BaseMultiSelectionListLinkView
 * @extends View.Views.Base.MultiSelectionListView
 */
({
    extendsFrom: 'MultiSelectionListView',

    initialize: function(options) {

        this._super('initialize', [options]);
        this.meta.selection = _.extend({}, options.meta.selection, {isLinkAction: true});
    },

    /**
     * Sets up events
     */
    initializeEvents: function() {
        this.context.on('selection-list:link:multi', this._selectMultipleAndClose, this);
        this.context.on('selection-list:select', this._refreshList, this);
    },

    /**
     * After a model is selected, refresh the list view and add the model to
     * selections.
     *
     * @private
     */
    _refreshList: function(model) {
        this.context.reloadData({
            recursive: false,
            error: function(error) {
                app.alert.show('server-error', {
                    level: 'error',
                    messages: 'ERR_GENERIC_SERVER_ERROR'
                });
            }
        });
    },

    /**
     * Select multiple models to link and fire the mass link event
     * @private
     */
    _selectMultipleAndClose: function() {
        var selections = this.context.get('mass_collection');
        if (selections) {
            this.layout.once('list:masslink:complete', this._closeDrawer, this);
            this.layout.trigger('list:masslink:fire');
        }
    },

    /**
     * Closes the drawer and then refreshes record page with new links.
     *
     * @private
     */
    _closeDrawer: function(model, data, response) {
        app.drawer.close();

        var context = this.options.context.get('recContext'),
            view = this.options.context.get('recView'),
            collectionOptions = context.get('collectionOptions') || {};

        if (context.has('parentModel')) {
            var parentModel = context.get('parentModel'),
                syncedAttributes = parentModel.getSyncedAttributes(),
                updatedAttributes = _.reduce(data.record, function(memo, val, key) {
                    if (!_.isEqual(syncedAttributes[key], val)) {
                        memo[key] = val;
                    }
                    return memo;
                }, {});
            parentModel.set(updatedAttributes);
            //Once parent model is reset, reset internal synced attributes as well
            parentModel.setSyncedAttributes(data.record);
        }

        context.get('collection').resetPagination();
        context.resetLoadFlag();
        context.set('skipFetch', false);
        //Reset limit on context so we don't 'over fetch' (lose pagination)
        if (collectionOptions.limit) {
            context.set('limit', collectionOptions.limit);
        }
        context.loadData({
            success: function() {
                view.layout.trigger('filter:record:linked');
            },
            error: function(error) {
                app.alert.show('server-error', {
                    level: 'error',
                    messages: 'ERR_GENERIC_SERVER_ERROR'
                });
            }
        });
    },
})
