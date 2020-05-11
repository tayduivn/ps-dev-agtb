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
 * @class View.Views.Base.MarketingExtrasView
 * @alias SUGAR.App.view.views.BaseMarketingExtrasView
 * @extends View.View
 */
({
    /**
     * The URL for marketing content
     */
    marketingContentUrl: '',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.fetchMarketingContentUrl();
    },

    /**
     * Fetch the marketing content URL
     */
    fetchMarketingContentUrl: function() {
        var url = app.api.buildURL('login/marketingContentUrl', null, null, null);
        app.api.call('read', url, null, {
            success: _.bind(function(response) {
                this.marketingContentUrl = response;
                this.render();
            }, this),
        });
    },
})
