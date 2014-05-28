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
 * Actions for {@link View.Views.Base.FilterRowsView}.
 *
 * Part of {@link View.Layouts.Base.FilterpanelLayout}.
 *
 * @class View.Views.Base.FilterActionsView
 * @alias SUGAR.App.view.views.BaseFilterActionsView
 * @extends View.View
 */
({
    events: {
        'change input': 'filterNameChanged',
        'keyup input': 'filterNameChanged',
        'click a.reset_button': 'triggerReset',
        'click a.filter-close': 'triggerClose',
        'click a.save_button:not(.disabled)': 'triggerSave',
        'click a.delete_button:not(.hide)': 'triggerDelete'
    },

    className: 'filter-header',

    /**
     * @type {Boolean} `true` if the button is enabled, `false` otherwise.
     */
    saveState: false,

    /**
     * @type {Boolean} Whether or not to display the filter action buttons.
     */
    showActions: true,

    /**
     * @{inheritDoc}
     */
    initialize: function(opts) {
        this._super('initialize', [opts]);

        this.layout.on('filter:create:open', function(model) {
            this.toggle(model);
            var name = model ? model.get('name') : '';
            this.setFilterName(name);
        }, this);

        this.listenTo(this.layout, 'filter:toggle:savestate', this.toggleSave);
        this.listenTo(this.layout, 'filter:set:name', this.setFilterName);
        this.listenTo(this.context, 'change:filterOptions', this.render);

        this.before('render', this.setShowActions, null, this);
    },

    /**
     * This function sets the `showActions` object on the controller.
     * `true` when `show_actions` is set to `true` on the `filterOptions`
     * object on the context (originating from filterpanel metadata),
     * `false` otherwise.
     */
    setShowActions: function() {
        var filterOptions = this.context.get('filterOptions') || {};
        this.showActions = !!filterOptions.show_actions;
    },

    /**
     * Get the filter name.
     *
     * @return {String} The value of the input.
     */
    getFilterName: function() {
        return this.$('input').val();
    },

    /**
     * Shows or hides this view.
     *
     * This view will be hidden when the filter is a template that is populated
     * on the fly.
     *
     * @param {Data.Bean} filter The filter being edited.
     */
    toggle: function(filter) {
        this.$el.toggleClass('hide', !!filter.get('is_template'));
    },

    /**
     * Set input value and hide the delete button if we're clearing the name.
     *
     * @param {String} name The filter name.
     */
    setFilterName: function(name) {
        var input = this.$('input').val(name);
        //Call placeholder() because IE9 does not support placeholders.
        if (_.isFunction(input.placeholder)) {
            input.placeholder();
        }
        // We have this.context.editingFilter if we're setting the name.
        this.toggleDelete(!_.isEmpty(name));
    },

    /**
     * Fired when the filter name changed.
     *
     * @param {Event} event The `change` event.
     */
    filterNameChanged: _.debounce(function(event) {
        if (this.disposed || !this.context.editingFilter) {
            return;
        }

        var name = this.getFilterName();
        this.context.editingFilter.set('name', name);
        this.layout.trigger('filter:toggle:savestate', true);

        if (this.layout.getComponent('filter-rows')) {
            this.layout.getComponent('filter-rows').saveFilterEditState();
        }
    }, 200),

    /**
     * Toggle delete button.
     *
     * @param {Boolean} enable `true` to enable the button, `false` otherwise.
     */
    toggleDelete: function(enable) {
        this.$('.delete_button').toggleClass('hide', !enable);
    },

    /**
     * Toggle save button.
     *
     * @param {Boolean} enable `true` to enable the button, `false` otherwise.
     */
    toggleSave: function(enable) {
        this.saveState = _.isUndefined(enable) ? !this.saveState : !!enable;
        var isEnabled = this.getFilterName() && this.saveState;
        this.$('.save_button').toggleClass('disabled', !isEnabled);
    },

    /**
     * Handler for canceling form editing.
     *
     * @triggers filter:apply to apply the previous filter definition.
     * @triggers filter:select:filter to switch back to the default filter.
     * @triggers filter:create:close to close the filter creation form.
     */
    triggerClose: function() {
        var filter = this.context.editingFilter,
            filterLayout = this.layout.getComponent('filter'),
            id = filter.get('id'),
            changedAttributes = filter.changedAttributes(filter.getSyncedAttributes());

        //Apply the previous filter definition if something has changed meanwhile
        if (changedAttributes && changedAttributes.filter_definition) {
            filter.revertAttributes();
            this.layout.trigger(
                /**
                 * @event
                 * See {@link View.Layouts.Base.FilterPanelLayout#filter:apply}.
                 */
                'filter:apply', null, filter.get('filter_definition'));
        }
        if (!id) {
            filterLayout.clearLastFilter(this.layout.currentModule, filterLayout.layoutType);
            filterLayout.trigger(
                /**
                 * @event
                 * See {@link View.Layouts.Base.FilterLayout#filter:select:filter}.
                 */
                'filter:select:filter', filterLayout.filters.defaultFilterFromMeta);
            return;
        }
        this.layout.trigger(
            /**
             * @event
             * See {@link View.Layouts.Base.FilterLayout#filter:create:close}.
             */
            'filter:create:close');
    },

    /**
     * Call a method on filter-rows to reset filter values.
     */
    triggerReset: function() {
        this.layout.getComponent('filter-rows').resetFilterValues();
    },

    /**
     * Trigger `filter:create:save` to save the created filter.
     */
    triggerSave: function() {
        var filterName = this.getFilterName();
        this.context.trigger('filter:create:save', filterName);
    },

    /**
     * Trigger `filter:create:delete` to delete the created filter.
     */
    triggerDelete: function() {
        this.layout.trigger('filter:create:delete');
    }
})
