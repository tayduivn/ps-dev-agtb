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
        this.model.on('change:product_bundles', this._onProductBundleChange, this);
        this.context.on('quotes:group:create', this._onCreateQuoteGroup, this);
        this.context.on('quotes:group:delete', this._onDeleteQuoteGroup, this);
    },

    /**
     * Handler for when quote_data changes on the model
     */
    _onProductBundleChange: function(model, productBundles) {
        this.records = productBundles.records;

        // fixme: SFA-4399 will add "groupless" rows in here before the groups

        _.each(this.records, function(bundleData) {
            if (!_.contains(this.groupIds, bundleData.id)) {
                this.groupIds.push(bundleData.id);
                this._addQuoteGroupToLayout(bundleData);
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
    _addQuoteGroupToLayout: function(bundleData) {
        bundleData = bundleData || {};

        var groupModel = app.data.createBean('ProductBundles', bundleData);
        var groupLayout = app.view.createLayout({
            context: this.context,
            meta: this.quoteDataGroupMeta,
            type: 'quote-data-group',
            layout: this,
            module: 'Quotes',
            loadModule: 'ProductBundles',
            model: groupModel
        });

        groupLayout.initComponents(undefined, undefined, 'ProductBundles');
        this.addComponent(groupLayout);
    },

    /**
     * Handles the quotes:group:create event
     * Creates a new empty quote data group and renders the groups
     *
     * @private
     */
    _onCreateQuoteGroup: function() {
        var highestPositionBundle = _.max(this.records, function(record) {
            return record.position;
        });

        app.alert.show('adding_bundle_alert', {
            level: 'info',
            autoClose: false,
            messages: app.lang.get('LBL_ADDING_BUNDLE_ALERT_MSG', 'Quotes')
        });

        app.api.relationships('create', 'Quotes', {
            'id': this.model.get('id'),
            'link': 'product_bundles',
            'related': {
                position: highestPositionBundle.position + 1
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

        this.records.push(newBundleData.related_record);
        this._addQuoteGroupToLayout(newBundleData.related_record);
        this.render();
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

        var removeIndex = -1;
        this.records = _.each(this.records, function(record, index) {
            if (record.id === groupId) {
                removeIndex = index;
            }
        });

        if (removeIndex !== -1) {
            // remove the record from this.records
            this.records.splice(removeIndex, 1);
        }

        // remove the id from this.groupIds
        this.groupIds = _.without(this.groupIds, groupId);

        // dispose the group
        groupToDelete.dispose();
    }
})
