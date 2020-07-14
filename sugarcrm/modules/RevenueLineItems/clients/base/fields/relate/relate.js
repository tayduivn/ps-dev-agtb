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
 * @class View.Fields.Base.RevenueLineItems.RelateField
 * @alias SUGAR.App.view.fields.BaseRevenueLineItemsRelateField
 * @extends View.Fields.Base.RelateField
 */
({
    extendsFrom: 'BaseRelateField',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        // deleting for RLI create when there is no account_id.
        if (options && options.def.filter_relate && !options.model.has('account_id')) {
            delete options.def.filter_relate;
        }

        this._super('initialize', [options]);
    },

    // BEGIN SUGARCRM flav=ent ONLY
    /**
     * Formats the filter options for add_on_to_name field.
     *
     * @param {boolean} force `true` to force retrieving the filter options whether or not it is available in memory.
     * @return {Object} The filter options.
     */
    getFilterOptions: function(force) {
        if (this.name && this.name === 'add_on_to_name' &&
            this.model && !_.isEmpty(this.model.get('account_id'))) {
            return new app.utils.FilterOptions()
                .config({
                    'initial_filter': 'add_on_plis',
                    'initial_filter_label': 'LBL_PLI_ADDONS',
                    'filter_populate': {
                        'account_id': [this.model.get('account_id')]
                    },
                })
                .format();
        } else {
            return this._super('getFilterOptions', [force]);
        }
    },
    // END SUGARCRM flav=ent ONLY

    setValue: function(models) {
        if (!models) {
            return;
        }
        var userCurrency = app.user.getCurrency();
        var createInPreferred = userCurrency.currency_create_in_preferred;
        var currencyFields;
        var currencyFromRate;

        if (this.name === 'product_template_name' && createInPreferred) {
            // get any currency fields on the model
            currencyFields = _.filter(this.model.fields, function(field) {
                return field.type === 'currency';
            });
            currencyFromRate = models.base_rate;
            models.currency_id = userCurrency.currency_id;
            models.base_rate = userCurrency.currency_rate;

            _.each(currencyFields, function(field) {
                // if the field exists on the model, convert the value to the new rate
                if (models[field.name] && field.name.indexOf('_usdollar') === -1) {
                    models[field.name] = app.currency.convertWithRate(
                        models[field.name],
                        currencyFromRate,
                        userCurrency.currency_rate
                    );
                }
            }, this);
        }
        this._super('setValue', [models]);
    }
})
