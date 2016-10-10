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
 * @class View.Views.Base.Quotes.RecordView
 * @alias SUGAR.App.view.views.BaseQuotesRecordView
 * @extends View.Views.Base.RecordView
 */
({
    extendsFrom: 'RecordView',

    /**
     * Track the calculated fields from the model to be used when checking for unsaved changes
     *
     * @type {Array}
     */
    calculatedFields: [],

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this.plugins = _.union(this.plugins || [], ['HistoricalSummary', 'QuotesViewSaveHelper']);
        this._super('initialize', [options]);

        // get all the calculated fields from the model
        this.calculatedFields = _.chain(this.model.fields)
            .where({calculated: true})
            .pluck('name')
            .value();
    },

    /**
     * @inheritdoc
     */
    getCustomSaveOptions: function(options) {
        options = options || {};
        var returnObject = {};

        // get the value that the server sent back
        var syncedValue = this.model.getSynced('currency_id');

        // has the currency_id changed?
        if (this.model.get('currency_id') !== syncedValue) {
            // make copy of original function we are extending
            var origSuccess = options.success;
            // only do this if the currency_id field actually changes
            returnObject = {
                success: _.bind(function() {
                    if (_.isFunction(origSuccess)) {
                        origSuccess.apply(this, arguments);
                    }
                    // create the payload
                    var bulkSaveRequests = this._createBulkBundlesPayload();
                    // send the payload
                    this._sendBulkBundlesUpdate(bulkSaveRequests);
                }, this)
            };
        }

        return returnObject;
    },

    /**
     * Utility method to create the payload that will be send to the server via the bulk api call
     * to update all the product bundles currencies
     * @private
     */
    _createBulkBundlesPayload: function() {
        // loop over all the bundles and create the requests
        var bundles = this.model.get('bundles');
        var bulkSaveRequests = [];
        var url;
        bundles.each(function(bundle) {
            // if the bundle is new, don't try and save it
            if (!bundle.isNew()) {
                // create the update url
                url = app.api.buildURL(bundle.module, 'update', {
                    id: bundle.get('id')
                });

                // save the request with the two fields that need to be updated
                // on the product bundle
                bulkSaveRequests.unshift({
                    url: url.substr(4),
                    method: 'PUT',
                    data: {
                        currency_id: bundle.get('currency_id'),
                        base_rate: bundle.get('base_rate')
                    }
                });
            }
        });

        return bulkSaveRequests;
    },

    /**
     * Send the payload via the bulk api
     * @param {Array} bulkSaveRequests
     * @private
     */
    _sendBulkBundlesUpdate: function(bulkSaveRequests) {
        if (!_.isEmpty(bulkSaveRequests)) {
            app.api.call(
                'create',
                app.api.buildURL(null, 'bulk'),
                {
                    requests: bulkSaveRequests
                },
                {
                    success: _.bind(this._onBulkBundlesUpdateSuccess, this)
                }
            );
        }
    },

    /**
     * Update the bundles when the results from the bulk api call
     * @param {Array} bulkResponses
     * @private
     */
    _onBulkBundlesUpdateSuccess: function(bulkResponses) {
        var bundles = this.model.get('bundles');
        var bundle;
        _.each(bulkResponses, function(record) {
            bundle = bundles.get(record.contents.id);
            if (bundle) {
                bundle.setSyncedAttributes(record.contents);
                bundle.set(record.contents);
            }
        }, this);
    },

    /**
     * @inheritdoc
     */
    hasUnsavedChanges: function() {
        return this.hasUnsavedQuoteChanges();
    }
})
