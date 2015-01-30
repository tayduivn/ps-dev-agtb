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
 * This view displays the selected records at the top of a selection list. It
 * also allows to unselect them.
 *
 * @class View.Views.Base.SelectionListContextView
 * @alias SUGAR.App.view.views.BaseSelectionListContextView
 * @extends View.View
 */

({
    className: 'selection-context',
    events: {
        'click [data-close-pill]': 'closePill',
        'click .reset_button': 'removeAllPills'
    },

    plugins: ['EllipsisInline'],

    /**
     * {@inheritDoc}
     */
    initialize: function(options) {
        this.pills = [];
        app.view.View.prototype.initialize.call(this, options);
     },

    /**
     * Adds a pill in the template.
     *
     * @param {Model} model The model corresponding to the pill to add.
     */
    addPill: function(model) {
        var name = !!model.name || model.get('name');
        this.pills.push({id: model.id, name: name});
        this.render();
    },

    /**
     * Removes a pill from the template.
     *
     * @param {Model} model The model corresponding to the pill to remove.
     */
    removePill: function(model) {
        this.pills = _.filter(this.pills, function(pill) {return pill.id !== model.id});
        this.render();
    },

    /**
     * Removes all the pills and sends an event to clear the mass collection.
     */
    removeAllPills: function() {
        if (this.$(event.target).hasClass('disabled')) return;
        this.pills = [];
        this.render();
        this.context.trigger('mass_collection:clear');
    },

    /**
     * Resets the pills to match the mass collection. Useful to update pills
     * on mass collection reset.
     *
     * @param {Object[]} models The models to add to pills.
     */
    resetPills: function(models) {
        this.pills = _.map(models, function(model) {return {id: model.id, name: model.get('name')}});
        this.render();
    },

    /**
     * Click handler for the `close` button on a pill. It removes the pill and
     * triggers an event to remove it the model from the mass collection.
     *
     * @param {Event} event The click event.
     */
    closePill: function(event) {
        var modelId = this.$(event.target).closest('.select2-search-choice').data('id').toString();
        this.removePill({id: modelId});
        var massCollection = this.context.get('mass_collection');
        if (!massCollection) {
            return;
        }
        var model = _.find(massCollection.models, function(model) {
            return model.id === modelId;
        });

        this.context.trigger('mass_collection:remove', model);
    },

    /**
     * @inheritDoc
     * @private
     */
    _render: function() {
        this._super('_render');
        var massCollection = this.context.get('mass_collection');
        if (!massCollection) {
            return;
        }
        this.stopListening(massCollection);

        this.listenTo(massCollection, 'add', this.addPill);
        this.listenTo(massCollection, 'remove', this.removePill);
        this.listenTo(massCollection, 'reset', this.resetPills);
    },

    /**
     * @inheritDoc
     */
    unbind: function() {
        var massCollection = this.context.get('mass_collection');
        if (!massCollection) {
            return;
        }
        this.stopListening(massCollection);
        this._super('unbind');
    }
})
