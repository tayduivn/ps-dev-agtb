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

        //Allow multiselect if allowed and for One to Multi relationship.
        if (this.oneToMany || this.multiSelect) {
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

        this.events = _.extend({}, this.events, {
            'click .search-and-select .single': 'triggerCheck'
        });

        this.initializeEvents();
    },

    /**
     * Checks the checkbox when the row is clicked.
     *
     * @param {object} event
     */
    triggerCheck: function(event) {
        //Ignore inputs and links/icons, because those already have defined effects
        if (!($(event.target).is('a,i,input'))) {
            if (this.oneToMany || this.multiSelect) {
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
     * Sets up events.
     *
     * Override this method to setup events for subclasses.
     */
    initializeEvents: function() {
    },

    /**
     * Selects multiple records and closes the drawer.
     *
     * Override this method in subclasses.
     *
     * @protected
     */
    _selectMultipleAndClose: function() {
    },

    /**
     * Selected from list. Closes the drawer.
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
