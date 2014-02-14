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
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */
({
    /**
     * Actions for BaseFilterRowsViews
     * Part of BaseFilterpanelLayout
     *
     * @class BaseFilterActionsView
     * @extends View
     */
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
     * @property {Boolean} saveState `true` if the button is enabled, `false`
     *   otherwise.
     */
    saveState: false,

    /**
     * @{inheritDoc}
     */
    initialize: function(opts) {
        app.view.View.prototype.initialize.call(this, opts);

        this.layout.on('filter:create:open', function(model) {
            var name = model ? model.get('name') : '';
            this.setFilterName(name);
        }, this);

        this.listenTo(this.layout, 'filter:toggle:savestate', this.toggleSave);
        this.listenTo(this.layout, 'filter:set:name', this.setFilterName);
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
        // We have this.layout.editingFilter if we're setting the name.
        this.toggleDelete(!_.isEmpty(name));
    },

    /**
     * Fired when the filter name changed.
     *
     * @param {Event} event The `change` event.
     */
    filterNameChanged: _.debounce(function(event) {
        if (this.disposed) {
            return;
        }
        this.layout.trigger('filter:toggle:savestate', true);
        if (this.layout.getComponent('filter-rows')) {
            this.layout.getComponent('filter-rows').saveFilterEditState();
        }
    }, 400),

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
     * Trigger `filter:create:close` to close the filter create panel.
     */
    triggerClose: function() {
        var id = this.layout.editingFilter.get('id');

        //Check the current filter definition
        var filterDef = this.layout.getComponent('filter-rows').buildFilterDef();
        //Apply the previous filter definition if something has changed meanwhile
        if (!_.isEqual(this.layout.editingFilter.get('filter_definition'), filterDef)) {
            this.layout.trigger('filter:apply', null, this.layout.editingFilter.get('filter_definition'));
        }
        this.layout.getComponent('filter').trigger('filter:create:close', true, id);
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
        this.layout.trigger('filter:create:save', filterName);
    },

    /**
     * Trigger `filter:create:delete` to delete the created filter.
     */
    triggerDelete: function() {
        this.layout.trigger('filter:create:delete');
    }
})
