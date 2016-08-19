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
 * @class View.Layouts.Base.ProductBundles.QuoteDataGroupLayout
 * @alias SUGAR.App.view.layouts.BaseProductBundlesQuoteDataGroupLayout
 * @extends View.Views.Base.Layout
 */
({
    /**
     * @inheritdoc
     */
    tagName: 'tbody',

    /**
     * @inheritdoc
     */
    className: 'quote-data-group',

    /**
     * The colspan value for the list
     */
    listColSpan: 0,

    /**
     * This is the ProductBundle ID from the model set here on the component
     * for easier access by parent layouts
     */
    groupId: undefined,

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        // set the groupID to the model ID
        this.groupId = this.model.get('id');
        // set this collection to the product_bundle_items collection
        this.collection = this.model.get('product_bundle_items');

        var listMeta = app.metadata.getView('Products', 'quote-data-group-list');
        if (listMeta && listMeta.panels && listMeta.panels[0].fields) {
            this.listColSpan = listMeta.panels[0].fields.length;
        }
    },

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this.model.on('change:product_bundle_items', this.render, this);
    },

    /**
     * @inheritdoc
     */
    _render: function() {
        this._super('_render');

        // add the group id to the bundle level tbody
        this.$el.attr('data-group-id', this.groupId);

        // set the product bundle ID on all the QLI/Notes rows
        this.$('tr.quote-data-group-list').attr('data-group-id', this.groupId);
    }
})
