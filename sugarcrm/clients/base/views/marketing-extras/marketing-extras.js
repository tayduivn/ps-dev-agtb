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
    marketingContentUrl: '',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.fetchMarketingExtras();
    },

    /**
     * Retrieve marketing extras URL from login content endpoint
     */
    fetchMarketingExtras() {
        var config = app.metadata.getConfig();
        this.showMarketingContent = config.marketingExtrasEnabled;
        if (this.showMarketingContent) {
            var language = app.user.getLanguage();
            var url = app.api.buildURL('login/content', null, null, {selected_language: language});
            app.api.call('read', url, null, {
                success: _.bind(function(contents) {
                    if (contents && !_.isEmpty(contents.content_url)) {
                        this.marketingContentUrl = contents.content_url;
                        this.render();
                    }
                }, this)
            });
        }
    },
})
