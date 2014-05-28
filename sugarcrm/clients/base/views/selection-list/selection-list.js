/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Views.Base.SelectionListView
 * @alias SUGAR.App.view.views.BaseSelectionListView
 * @extends View.Views.Base.FlexListView
 */
({
    extendsFrom: 'FlexListView',
    events: {
        'click .search-and-select .single': 'triggerCheck'
    },

    initialize: function(options) {
        this.plugins = _.union(this.plugins, ['ListColumnEllipsis', 'ListRemoveLinks']);
        //setting skipFetch to true so that loadData will not run on initial load and the filter load the view.
        options.context.set('skipFetch', true);
        options.meta = options.meta || {};

        this.oneToMany = options.context.get('recLink') ?
                app.data.canHaveMany(app.controller.context.get('module'), options.context.get('recLink')) :
                false;

        //One to Multi relationship; allow multi linking
        if (this.oneToMany) {
            options.meta.selection = {
                type: 'multi',
                isLinkAction: true
            };
        } else {
            options.meta.selection = {
                type: 'single',
                label: 'LBL_LINK_SELECT',
                isLinkAction: true
            };
        }

        this._super('initialize', [options]);

        if (this.oneToMany) {
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
        this.initializeEvents();
    },

    /**
     * check the checkbox when the row is clicked
     * @param {object} event
     */
    triggerCheck: function(event) {
        //Ignore inputs and links/icons, because those already have defined effects
        if (!($(event.target).is('a,i,input'))) {
            if (this.oneToMany) {
                //simulate click on the input for this row
                var checkbox = $(event.currentTarget).find('input[name="check"]');
                checkbox[0].click();
            } else {
                var radioButton = $(event.currentTarget).find('.selection[type="radio"]');
                radioButton[0].click();
            }
        }
    },

    /**
     * Override to setup events for subclasses
     */
    initializeEvents: function() {
        if (this.oneToMany) {
            this.context.on('selection-list:link:multi', this._selectMultipleAndClose, this);
            this.context.on('selection-list:select', this._refreshList, this);
        } else {
            this.context.on('change:selection_model', this._selectAndClose, this);
            this.context.on('selection-list:select', this._selectAndCloseImmediately, this);
        }
    },

    /**
     * After a model is selected, refresh the list view and add the model to selections
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
     * Close drawer and then refresh record page with new links
     * @private
     */
    _closeDrawer: function() {
        app.drawer.close();
        var context = this.options.context.get('recContext'),
            view = this.options.context.get('recView'),
            collectionOptions = context.get('collectionOptions') || {};
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
     * Selected from list. Close the drawer.
     *
     * @param {object} context
     * @param {object} selectionModel
     * @private
     */
    _selectAndClose: function(context, selectionModel) {
        if (selectionModel) {
            this.context.unset('selection_model', {silent: true});
            app.drawer.close(this._getModelAttributes(selectionModel));
        }
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
    },

    /**
     * Return attributes given a model with ACL check
     *
     * @param {object} model
     * @return {object} attributes
     * @private
     */
    _getModelAttributes: function(model) {
        var attributes = {
            id: model.id,
            value: model.get('name')
        };

        //only pass attributes if the user has view access
        _.each(model.attributes, function(value, field) {
            if (app.acl.hasAccessToModel('view', model, field)) {
                attributes[field] = attributes[field] || model.get(field);
            }
        }, this);

        return attributes;
    },

    /**
     * Add Preview button on the actions column on the right.
     */
    addActions: function() {
        this._super('addActions');
        if (this.meta.showPreview !== false) {
            this.rightColumns.push({
                type: 'rowaction',
                css_class: 'btn',
                tooltip: 'LBL_PREVIEW',
                event: 'list:preview:fire',
                icon: 'icon-eye-open'
            });
        } else {
            this.rightColumns.push({});
        }
    }
})
