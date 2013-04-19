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
    extendsFrom: 'ListBottomView',

    totals: {},

    initialize: function(options) {
        app.view.views.ListBottomView.prototype.initialize.call(this, options);
    },

    bindDataChange: function() {
        app.view.views.ListBottomView.prototype.bindDataChange.call(this);

        this.collection.on('reset change', function() {
            this.calculateTotals();
        }, this)
    },

    calculateTotals: function() {
        // add up all the currency fields
        // get the list of fields from the first model. if the first model doesn't exist then just bail
        if (_.isUndefined(this.collection.models[0])) {
            return;
        }

        var fields = _.filter(this.collection.models[0].fields, function(field) {
                return field.type === 'currency'
            }),
            fieldNames = [];

        _.each(fields, function(field) {
            fieldNames.push(field.name);
            this.totals[field.name] = 0;
        }, this);

        this.collection.each(function(model) {
            _.each(fieldNames, function(field) {
                // convert the value to base
                var val = model.get(field);
                if(_.isUndefined(val) || _.isNaN(val)) {
                    return;
                }
                val = app.currency.convertWithRate(val, model.get('base_rate'));
                this.totals[field] = app.math.add(this.totals[field], val);
            }, this)
        }, this);
    }
})
