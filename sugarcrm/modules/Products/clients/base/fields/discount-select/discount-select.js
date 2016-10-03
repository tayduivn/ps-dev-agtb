/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Fields.Base.Products.DiscountSelectField
 * @alias SUGAR.App.view.fields.BaseProductsDiscountSelectField
 * @extends View.Fields.Base.ActiondropdownField
 */
({
    extendsFrom: 'BaseActiondropdownField',

    /**
     * The current currency object
     */
    currentCurrency: undefined,

    /**
     * The current symbol to use in place of the caret dropdown icon
     */
    currentDropdownSymbol: undefined,

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.updateCurrencyStrings();
    },

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this._super('bindDataChange');

        this.context.on('button:discount_select_change:click', this.onDiscountChanged, this);
        this.model.on('change:currency_id', this.updateCurrencyStrings, this);
    },

    /**
     * @inheritdoc
     */
    _render: function() {
        var $dropdown;

        this._super('_render');

        $dropdown = this.$('.fa');
        $dropdown.removeClass(this.caretIcon);
        $dropdown.text(this.currentDropdownSymbol);
    },

    /**
     * Called when a user clicks the Amount or Percent dropdown buttons
     *
     * @param {Data.Bean} model The model of the row that was changed
     * @param {View.Field} field The field that triggered the event
     * @param evt {Event} The click event
     */
    onDiscountChanged: function(model, field, evt) {
        var isPercent = false;
        if (this.model === model) {
            // only update for the row the event was triggered in
            if (field.name === 'select_discount_percent_button') {
                isPercent = true;
            }
            this.model.set(this.name, isPercent);

            this.updateDropdownSymbol();
        }
    },

    /**
     * Updates the dropdown icon symbol
     */
    updateDropdownSymbol: function() {
        if (this.model.get(this.name) === false) {
            this.currentDropdownSymbol = this.currentCurrency.symbol;
        } else {
            this.currentDropdownSymbol = '%';
        }

        this.render();
    },

    /**
     * Gets the current row model's currency_id and updates the labels for the buttons
     */
    updateCurrencyStrings: function() {
        var btn;
        var currentCurrencyLabel;

        if (this.model.has('currency_id')) {
            this.currentCurrency = app.metadata.getCurrency(this.model.get('currency_id'));
            currentCurrencyLabel = this.currentCurrency.symbol + ' ' + this.currentCurrency.name;

            if (app.lang.direction !== 'ltr') {
                currentCurrencyLabel = this.currentCurrency.name + ' ' + this.currentCurrency.symbol;
            }

            btn = _.find(this.def.buttons, function(button) {
                return button.name === 'select_discount_amount_button';
            });

            // update the button label to the current row currency
            btn.label = currentCurrencyLabel;

            // make sure the dropdown symbol is updated
            this.updateDropdownSymbol();
        }
    }
})
