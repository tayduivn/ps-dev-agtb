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
        'click [data-unselect-pill]': 'closePill'
    },

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
        this._render();
    },

    /**
     * Click handler for the `close` button on a pill.
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

    _render: function() {
        this._super('_render');
        var massCollection = this.context.get('mass_collection');
        if (!massCollection) {
            return;
        }
        this.stopListening(massCollection);

        this.listenTo(massCollection, 'add', this.addPill);
        this.listenTo(massCollection, 'remove', this.removePill);
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
