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
     * An Array of ProductBundle IDs currently in the Quote
     */
    groupIds: undefined,

    /**
     * Holds the layout metadata for ProductBundlesQuoteDataGroupLayout
     */
    quoteDataGroupMeta: undefined,

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.groupIds = [];
        this.quoteDataGroupMeta = app.metadata.getLayout('ProductBundles', 'quote-data-group');
    },

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this.model.on('change:bundles', this._onProductBundleChange, this);
        this.context.on('quotes:group:create', this._onCreateQuoteGroup, this);
        this.context.on('quotes:group:delete', this._onDeleteQuoteGroup, this);
    },

    /**
     * Handler for when quote_data changes on the model
     */
    _onProductBundleChange: function(productBundles) {
        // fixme: SFA-4399 will add "groupless" rows in here before the groups
        // after adding and deleting the change event is like ite change for the model, where the
        // model is the first param and not the actual value it's self.
        if (productBundles instanceof Backbone.Model) {
            productBundles = productBundles.get('bundles');
        }

        productBundles.each(function(bundle) {
            if (!_.contains(this.groupIds, bundle.get('id'))) {
                this.groupIds.push(bundle.get('id'));
                this._addQuoteGroupToLayout(bundle);
            }
        }, this);

        this.render();
    },

    /**
     * Adds the actual quote-data-group layout component to this layout
     *
     * @param {Object} [bundleData] The ProductBundle data object - defaults to empty Object
     * @private
     */
    _addQuoteGroupToLayout: function(bundle) {
        var pbContext = this.context.getChildContext({module: 'ProductBundles'});
        pbContext.prepare();
        var groupLayout = app.view.createLayout({
            context: pbContext,
            meta: this.quoteDataGroupMeta,
            type: 'quote-data-group',
            layout: this,
            module: 'ProductBundles',
            model: bundle
        });

        groupLayout.initComponents(undefined, pbContext, 'ProductBundles');
        this.addComponent(groupLayout);
    },

    /**
     * Handles the quotes:group:create event
     * Creates a new empty quote data group and renders the groups
     *
     * @private
     */
    _onCreateQuoteGroup: function() {
        var bundles = this.model.get('bundles');
        var nextPosition = 0;
        var highestPositionBundle = bundles.max(function(bundle) {
            return bundle.get('position');
        });

        // handle on the off chance that no bundles exist on the quote.
        if (!_.isEmpty(highestPositionBundle)) {
            nextPosition = parseInt(highestPositionBundle.get('position')) + 1;
        }

        app.alert.show('adding_bundle_alert', {
            level: 'info',
            autoClose: false,
            messages: app.lang.get('LBL_ADDING_BUNDLE_ALERT_MSG', 'Quotes')
        });

        app.api.relationships('create', 'Quotes', {
            'id': this.model.get('id'),
            'link': 'product_bundles',
            'related': {
                position: nextPosition
            }
        }, null, {
            success: _.bind(this._onCreateQuoteGroupSuccess, this)
        });
    },

    /**
     * Success callback handler for when a quote group is created
     *
     * @param {Object} newBundleData The new Quote group data
     * @private
     */
    _onCreateQuoteGroupSuccess: function(newBundleData) {
        app.alert.dismiss('adding_bundle_alert');

        app.alert.show('added_bundle_alert', {
            level: 'success',
            autoClose: true,
            messages: app.lang.get('LBL_ADDED_BUNDLE_SUCCESS_MSG', 'Quotes')
        });

        var bundles = this.model.get('bundles');
        // make sure that the product_bundle_items array is there
        if (_.isUndefined(newBundleData.related_record.product_bundle_items)) {
            newBundleData.related_record.product_bundle_items = [];
        }
        // now add the new record to the bundles collection
        bundles.add(newBundleData.related_record);
    },

    /**
     * Deletes the passed in ProductBundle
     *
     * @param {ProductBundlesQuoteDataGroupLayout} groupToDelete The group layout to delete
     * @private
     */
    _onDeleteQuoteGroup: function(groupToDelete) {
        var groupId = groupToDelete.model.get('id');
        var groupName = groupToDelete.model.get('name') || '';

        app.alert.show('confirm_delete_bundle', {
            level: 'confirmation',
            autoClose: false,
            messages: app.lang.get('LBL_DELETING_BUNDLE_CONFIRM_MSG', 'Quotes', {
                groupName: groupName
            }),
            onConfirm: _.bind(this._onDeleteQuoteGroupConfirm, this, groupId, groupName, groupToDelete)
        });
    },

    /**
     * Handler for when the delete quote group confirm box is confirmed
     *
     * @param {string} groupId The model ID of the deleted group
     * @param {string} groupName The model name of the deleted group
     * @param {View.Layout} groupToDelete The Layout for the deleted group
     * @private
     */
    _onDeleteQuoteGroupConfirm: function(groupId, groupName, groupToDelete) {
        app.alert.show('deleting_bundle_alert', {
            level: 'info',
            autoClose: false,
            messages: app.lang.get('LBL_DELETING_BUNDLE_ALERT_MSG', 'Quotes', {
                groupName: groupName
            })
        });

        app.api.records('delete', 'ProductBundles', {
            id: groupId
        }, null, {
            success: _.bind(this._onDeleteQuoteGroupSuccess, this, groupId, groupToDelete)
        });
    },

    /**
     * Success callback when the quote group is deleted
     *
     * @param {string} groupId The model ID of the deleted group
     * @param {View.Layout} groupToDelete The Layout for the deleted group
     * @private
     */
    _onDeleteQuoteGroupSuccess: function(groupId, groupToDelete) {
        app.alert.dismiss('deleting_bundle_alert');

        app.alert.show('deleted_bundle_alert', {
            level: 'success',
            autoClose: true,
            messages: app.lang.get('LBL_DELETED_BUNDLE_SUCCESS_MSG', 'Quotes')
        });

        var bundles = this.model.get('bundles');
        bundles.remove(groupToDelete.model);

        this.groupIds = _.without(this.groupIds, groupId);

        // dispose the group
        groupToDelete.dispose();
    }
})
