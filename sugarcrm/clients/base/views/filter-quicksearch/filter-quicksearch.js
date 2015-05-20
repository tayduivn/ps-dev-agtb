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
 * View for doing a quick search.
 *
 * Part of {@link View.Layouts.Base.FilterLayout}.
 *
 * @class View.Views.Base.FilterQuicksearchView
 * @alias SUGAR.App.view.views.BaseFilterQuicksearchView
 * @extends View.View
 */
({
    events: {
        'keyup': 'throttledSearch',
        'paste': 'throttledSearch'
    },

    plugins: ['QuickSearchFilter'],

    className: 'table-cell full-width',

    /**
     * @override
     * @param {Object} opts
     */
    initialize: function(opts) {
        app.view.View.prototype.initialize.call(this, opts);
        this.listenTo(this.layout, 'filter:clear:quicksearch', this.clearInput);
        this.listenTo(this.layout, 'filter:quicksearch:apply', this.applyQuickSearch);
        this.listenTo(this.layout, 'filter:change:module', this.updatePlaceholder);

        //shortcut keys
        app.shortcuts.register({
            id: 'Filter:Search',
            keys: ['f i','ctrl+alt+9'],
            component: this,
            description: 'LBL_SHORTCUT_FILTER_SEARCH',
            handler: function() {
                if (this.$el.is(':visible')) {
                    this.$el.focus();
                }
            }
        });
    },

    /**
     * @inheritDoc
     *
     * Changes `this.$el` to point to the `<input>` element.
     */
    _renderHtml: function() {
        this._super('_renderHtml');
        this.setElement(this.$('input'));
    },

    /**
     * Fires the quick search.
     * @param {Event} [event] A keyup event.
     */
    throttledSearch: _.debounce(function(event) {
            this.applyQuickSearch();
    }, 400),

    /**
     * Retrieves the labels for the fields that are searchable in the
     * quicksearch.
     *
     * @param {string} moduleName The module name the fields belong to.
     * @param {string[]} fields The list of searchable fields.
     * @return {string[]} The list of labels.
     */
    getFieldLabels: function(moduleName, fields) {
        var moduleMeta = app.metadata.getModule(moduleName);
        var labels = [];

        _.each(_.flatten(fields), function(fieldName) {
            var fieldMeta = moduleMeta.fields[fieldName];
            if (fieldMeta) {
                labels.push(app.lang.get(fieldMeta.vname, moduleName).toLowerCase());
            }
        });

        return labels;
    },

    /**
     * Update quick search placeholder to Search by Field1, Field2, Field3 when the module changes
     * @param string linkModuleName
     * @param string linkModule
     */
    updatePlaceholder: function(linkModuleName, linkModule) {
        var label;
        this.toggleInput();
        if (!this.$el.hasClass('hide') && linkModule !== 'all_modules') {
            var fields = this.getModuleQuickSearchFields(linkModuleName),
                fieldLabels = this.getFieldLabels(linkModuleName, fields);
            label = app.lang.get('LBL_SEARCH_BY') + ' ' + fieldLabels.join(', ') + '...';
        } else {
            label = app.lang.get('LBL_BASIC_QUICK_SEARCH');
        }
        var input = this.$el.attr('placeholder', label);
        //Call placeholder() because IE9 does not support placeholders.
        if (_.isFunction(input.placeholder)) {
            input.placeholder();
        }
    },

    /**
     * Hide input if on Activities
     */
    toggleInput: function() {
        this.$el.toggleClass('hide', !!this.layout.showingActivities);
    },

    /**
     * Clear input
     */
    clearInput: function() {
        this.toggleInput();
        var input = this.$el.val('');
        //Call placeholder() because IE9 does not support placeholders.
        if (_.isFunction(input.placeholder)) {
            input.placeholder();
        }
        this.applyQuickSearch();
    },

    /**
     * Invokes the `filter:apply` event with the current value on the quicksearch field.
     */
    applyQuickSearch: function() {
        var newSearch = this.$el.val();
        if (this.currentSearch !== newSearch) {
            this.currentSearch = newSearch;
            this.layout.trigger('filter:apply', newSearch);
        }
    }
})
