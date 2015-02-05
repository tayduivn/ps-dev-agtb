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
 * The SelectionListView provides an easy way to select records from a list and
 * get selected items as an output. It's designed to be used in a drawer.
 *
 * The SelectionListView has a generic implementation and can be overriden for
 * particular uses.
 *
 * It has the following properties:
 *
 * - `isMultiSelect` A boolean that tells if the user can select multiple records.
 * - `maxSelectedRecords` The max number of records the user can select in the
 *    case of multiselect selecttion list.
 *
 * It has to be opened passing the following data in the drawer's context:
 *
 * - `module` {String} The module the list is related to.
 * - `fields` {Array} The fields to be displayed.
 * - `filterOptions` {Object} the filter options for the list view.
 *
 *  Example of usage:
 *
 *     app.drawer.open({
 *              layout: 'selection-list',
 *               context: {
 *                   module: this.getSearchModule(),
 *                   fields: this.getSearchFields(),
 *                   filterOptions: this.getFilterOptions(),
 *                   preselectedModelIds: _.clone(this.model.get(this.def.id_name)),
 *                   isMultiSelect: this.def.isMultiSelect,
 *                   maxSelectedRecords: this.maxSelectedRecords
 *               }
 *           }, _.bind(this.setValue, this));
 *     },
 *
 * @class View.Views.Base.SelectionListView
 * @alias SUGAR.App.view.views.BaseSelectionListView
 * @extends View.Views.Base.FlexListView
 */
({
    extendsFrom: 'FlexListView',

    initialize: function(options) {
        this.plugins = _.union(this.plugins, ['ListColumnEllipsis', 'ListRemoveLinks']);
        //setting skipFetch to true so that loadData will not run on initial load and the filter load the view.
        options.context.set('skipFetch', true);
        options.meta = options.meta || {};
        this.setSelectionMeta(options);
        this._super('initialize', [options]);

        this.events = _.extend({}, this.events, {
            'click .search-and-select .single': 'triggerCheck'
        });
        this.initializeEvents();
    },

    /**
     * Sets metadata proper to selection-list.
     *
     * @param options
     *
     * FIXME: SC-4075 will remove this method.
     */
    setSelectionMeta: function(options) {
        options.meta.selection = {
            type: 'single',
            label: 'LBL_LINK_SELECT',
            isSearchAndSelectAction: true
        };
    },

    /**
     * Checks the checkbox when the row is clicked.
     *
     * @param {object} event
     */
    triggerCheck: function(event) {
        //Ignore inputs and links/icons, because those already have defined effects
        if (!($(event.target).is('a,i,input'))) {
            var radioButton = $(event.currentTarget).find('.selection[type="radio"]');
            radioButton[0].click();
        }
    },

    /**
     * Sets up events.
     */
    initializeEvents: function() {
        this.context.on('change:selection_model', this._selectAndClose, this);
    },

    /**
     * Closes the drawer passing the selected model attributes to the callback.
     *
     * @param {object} context
     * @param {object} selectedModel The selected record.
     *
     * @protected
     */
    _selectAndClose: function(context, selectedModel) {
        if (selectedModel) {
            this.context.unset('selection_model', {silent: true});
            app.drawer.close(this._getModelAttributes(selectedModel));
        }
    },

    /**
     * Returns attributes given a model with ACL check.
     *
     * @param {object} model
     * @return {object} attributes
     *
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
     * Adds Preview button on the actions column on the right.
     */
    addActions: function() {
        this._super('addActions');
        if (this.meta.showPreview !== false) {
            this.rightColumns.push({
                type: 'preview-button',
                css_class: 'btn',
                tooltip: 'LBL_PREVIEW',
                event: 'list:preview:fire',
                icon: 'fa-eye'
            });
        } else {
            this.rightColumns.push({});
        }
    }
})
