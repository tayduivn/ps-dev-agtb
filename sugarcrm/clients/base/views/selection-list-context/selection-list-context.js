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
     * @inheritDoc
     */
    initialize: function(options) {
        this.pills = [];
        /**
         * The maximum number of pills that can be displayed.
         *
         * @property {number}
         */
        this.maxPillsDisplayed = 50;
        this._super('initialize', [options]);
     },

    /**
     * Adds a pill in the template.
     *
     * @param {Data.Bean} model The model corresponding to the pill to add.
     */
    addPill: function(model) {
        var name = model.get('name') || model.get('full_name');
        this.pills.push({id: model.id, name: name});
        this.render();
    },

    /**
     * Removes a pill from the template.
     *
     * @param {Data.Bean} model The model corresponding to the pill to remove.
     */
    removePill: function(model) {
        this.pills = _.reject(this.pills, function(pill) {
            return pill.id === model.id;
        });
        this.render();
    },

    /**
     * Removes all the pills and sends an event to clear the mass collection.
     *
     * @param {Event} The click event.
     */
    removeAllPills: function(event) {
        if (event) {
            if (this.$(event.target).hasClass('disabled')) {
                return;
            }
        }
        this.pills = [];
        this.render();
        this.context.trigger('mass_collection:clear');
    },

    /**
     * Resets the pills to match the mass collection. Useful to update pills
     * on mass collection reset.
     *
     * @param {Object[]} models The models to add to pills.
     *
     * FIXME: SC-4092 : Here we should add `models` in `this.pills`. To do this,
     * we need to get the `name` property in each model, we currently only
     * get `id`.
     */
    resetPills: function(models) {
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
        var model = this.massCollection.get(modelId);
        this.context.trigger('mass_collection:remove', model);
    },

    /**
     * @inheritDoc
     */
    _render: function() {

        if (this.pills.length > this.maxPillsDisplayed) {
            this.displayedPills = this.pills.slice(0, this.maxPillsDisplayed);
            this.tooManySelectedRecords = true;
            this.msgMaxPillsDisplayed = app.lang.get('TBL_MAX_PILLS_DISPLAYED', this.module, {
                maxPillsDisplayed: this.maxPillsDisplayed
            });
        } else {
            this.tooManySelectedRecords = false;
            this.displayedPills = this.pills;
        }

        this.massCollection = this.context.get('mass_collection');
        if (!this.massCollection) {
            return;
        }
        this._super('_render');
        this.stopListening(this.massCollection);

        this.listenTo(this.massCollection, 'add', this.addPill);
        this.listenTo(this.massCollection, 'remove', this.removePill);
        this.listenTo(this.massCollection, 'reset', this.resetPills);
    },

    /**
     * @inheritDoc
     */
    unbind: function() {
        this.stopListening(this.massCollection);
        this._super('unbind');
    }
})
