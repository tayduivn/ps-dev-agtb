/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
({
    extendsFrom: 'CreateView',
    currencyFields: [],

    initialize: function(options) {
        //reinitialize array on each init
        this.currencyFields = [];
        app.view.invokeParent(this, {type: 'view', name: 'create', method: 'initialize', args: [options]});

        //pull the fields in the panels that are editable currency fields
        _.each(options.meta.panels, function(panel) {
                _.each(panel.fields, function(field) {
                    //if the field is currency and not the known calculated field, add to the array
                    if (field.type == 'currency') {
                        this.currencyFields.push(field.name);
                    }
                }, this);
            }, this
        );
    },

    /**
     * Bind to model to make it so that it will re-render once it has loaded.
     */
    bindDataChange: function() {
        // TODO: Calling "across controllers" considered harmful .. please consider using a plugin instead.
        app.view.invokeParent(this, {type: 'view', name: 'create', method: 'bindDataChange'});
        this.model.on('change:base_rate', function() {
            _.debounce(this.convertCurrencyFields(this.model.previous("currency_id"), this.model.get("currency_id")), 500, true);
        }, this);
    },

    /**
     * convert all of the currency fields to the new currency
     * @param oldCurrencyId
     * @param newCurrencyId
     */
    convertCurrencyFields: function(oldCurrencyId, newCurrencyId) {
        //run through the editable currency fields and convert the amounts to the new currency
        _.each(this.currencyFields, function(currencyField) {
            if (!_.isUndefined(this.model.get(currencyField)) && currencyField != 'total_amount') {
                this.model.set(currencyField, app.currency.convertAmount(this.model.get(currencyField), oldCurrencyId, newCurrencyId), {silent: true});
            }
            this.model.trigger("change:" + currencyField);
        }, this);
    }
})
