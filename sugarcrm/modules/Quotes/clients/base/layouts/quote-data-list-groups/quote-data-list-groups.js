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
 * @class View.Layouts.Base.Quotes.QuoteDataListGroupsLayout
 * @alias SUGAR.App.view.layouts.BaseQuotesQuoteDataListGroupsLayout
 * @extends View.Views.Base.Layout
 */
({
    /**
     * @inheritdoc
     */
    tagName: 'table',

    /**
     * @inheritdoc
     */
    className: 'table dataTable quote-data-list-table',

    /**
     * Array of records from the Quote data
     */
    records: undefined,

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this.model.on('change:quote_data', this._onQuoteDataChange, this);
    },

    /**
     * Handler for when quote_data changes on the model
     */
    _onQuoteDataChange: function(model, quoteData) {
        var groupLayout;
        var groupMeta;
        var groupModel;

        this.records = quoteData.records;

        // fixme: SFA-4399 will add "groupless" rows in here before the groups

        groupMeta = app.metadata.getLayout('ProductBundles', 'quote-data-group');

        _.each(this.records, function(dataGroup) {
            groupModel = app.data.createBean('ProductBundles', dataGroup);
            groupLayout = app.view.createLayout({
                context: this.context,
                meta: groupMeta,
                type: 'quote-data-group',
                layout: this,
                module: 'Quotes',
                loadModule: 'ProductBundles',
                model: groupModel
            });

            groupLayout.initComponents(undefined, undefined, 'ProductBundles');
            this.addComponent(groupLayout);
        }, this);

        this.render();
    }
})
