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
 * @class View.Fields.Base.NotificationCenterAddressBaseField
 * @alias SUGAR.App.view.fields.BaseNotificationCenterBaseField
 * @extends View.Fields.Base.BaseField
 */
({
    extendsFrom: 'BaseField',

    /**
     * @property {Object} Attached events.
     */
    events: {},

    /**
     * @property {string} Append addresses field
     */
    appendAddressTag: 'input[name=append_address]',

    /**
     * @property {Array[]} List of carrier items.
     */
    items: null,

    /**
     * @property {string} Current carrier name.
     */
    carrier: null,

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.items = options.def.options;
        this.carrier = options.def.carrier;
        this.model.on('change:personal:carrier:' + this.carrier, this.showHideField, this);
        this.model.on('reset:all', this.render, this);
    },

    /**
     * @inheritdoc
     */
    render: function() {
        this._super('render');
        this.showHideField();
    },

    /**
     * If carrier gets enabled, show its address field, otherwise hide it.
     */
    showHideField: function() {
        var personal = this.model.get('personal').carriers;
        var global = this.model.get('global').carriers;

        if (personal[this.carrier].status === true &&
            global[this.carrier].status === true &&
            global[this.carrier].isConfigured === true) {
            this.show();
        } else {
            this.hide();
        }
    },

    /**
     * Build addresses values with labels. Used for checkboxes and radio buttons.
     */
    getFormattedValue: function() {
        var value = [];
        var selectedValue = this.model.get('selectedAddresses')[this.carrier] || [];
        _.each(this.items, function(val, key) {
            value.push({
                id: key,
                checked: _.contains(selectedValue, key),
                label: val
            });
        }, this);
        return this.format(value);
    },

    /**
     * Set selected values to model.
     * @param {number[]} selectedValues
     */
    setSelectedAddresses: function(selectedValues) {
        if (!_.isArray(selectedValues)) {
            selectedValues = [selectedValues];
        }
        var addresses = _.clone(this.model.get('selectedAddresses'));
        addresses[this.carrier] = selectedValues;
        this.model.set('selectedAddresses', addresses);
    }
});
