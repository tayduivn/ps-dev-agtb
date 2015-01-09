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
 * The SelectionListLinkView is a custom SelectionListView used in subpanels to link existing records.
 * As SelectionListView, it's designed to be opened in a drawer passing it the following data in the context:
 *  `module` The module the list is related with.
 *  `recParentModel` The parent model.
 *  `recLink`
 *  `recContext` The context of the element in which the drawer is called.
 *  `recView` The view of the element in which the drawer is called.
 *
 *  Example of usage:
 *
 *         app.drawer.open({
 *           layout: 'selection-list-link',
 *           context: {
 *               module: this.context.get('module'),
 *               recParentModel: this.context.get('parentModel'),
 *               recLink: this.context.get('link'),
 *               recContext: this.context,
 *               recView: this.view
 *           }
 *         }, function(model) {
 *            ....
 *         });
 *
 * @class View.Views.Base.SelectionListLinkView
 * @alias SUGAR.App.view.views.BaseSelectionListLinkView
 * @extends View.Views.Base.SelectionListView
 */
({
    extendsFrom: 'SelectionListView',

    initialize: function(options) {
        /**
         * Boolean to know if we can link multiple items.
         *
         * @type {Boolean}
         */
        this.isMultiSelect = options.context.get('recLink') ?
                app.data.canHaveMany(app.controller.context.get('module'), options.context.get('recLink')) :
                false;

        this._super('initialize', [options]);
        this.meta.selection = _.extend({}, options.meta.selection, {isLinkAction: true});

        if (this.isMultiSelect) {
            //Set up mass linker component
            var pageComponent = this.layout.getComponent('mass-link');
            if (!pageComponent) {
                pageComponent = app.view.createView({
                    context: this.context,
                    name: 'mass-link',
                    module: this.module,
                    primary: false,
                    layout: this.layout
                });
                this.layout.addComponent(pageComponent);
            }
            pageComponent.render();
        }
    },

    /**
     * Sets up events
     */
    initializeEvents: function() {
        if (this.isMultiSelect) {
            this.context.on('selection-list:link:multi', this._selectMultipleAndClose, this);
            this.context.on('selection-list:select', this._refreshList, this);
        } else {
            this.context.on('change:selection_model', this._selectAndClose, this);
            this.context.on('selection-list:select', this._selectAndCloseImmediately, this);
        }
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

    /**
     * Select the given model and close the drawer immediately.
     *
     * @param {object} model
     * @private
     */
    _selectAndCloseImmediately: function(model) {
        if (model) {
            app.drawer.closeImmediately(this._getModelAttributes(model));
        }
    }
})
