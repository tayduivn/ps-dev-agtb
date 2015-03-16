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
 * @class View.Views.Base.RevenueLineItems.CreateView
 * @alias SUGAR.App.view.views.RevenueLineItemsCreateView
 * @extends View.Views.Base.CreateView
 */
({
    extendsFrom: 'CreateView',

    initialize: function(options) {
        this._super("initialize", [options]);
        app.utils.hideForecastCommitStageField(this.meta.panels);
    },

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this.model.on('change:likely_case', this._handleLikelyChange, this);
        this._super('bindDataChange');
    },

    /**
     * Handle a change to likely value (requiring copy to unit price when empty).
     */
    _handleLikelyChange: function(new_model, val, options) {
        if (_.isEmpty(new_model.get('product_template_id')) && !_.isFinite(new_model.get('discount_price'))) {
            var quantity = new_model.get('quantity'),
                new_value = '';

            if (!_.isFinite(quantity) || parseFloat(quantity) === 0) {
                quantity = 1;
            }

            if (!_.isEmpty(val)) {
                new_value = app.math.div(val, quantity);
            }

            new_model.set({discount_price: new_value});
        }
    }
})
