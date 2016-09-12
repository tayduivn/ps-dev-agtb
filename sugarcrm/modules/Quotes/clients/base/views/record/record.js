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
        this.plugins = _.union(this.plugins || [], ['HistoricalSummary']);
        this._super('initialize', [options]);

        // get all the calculated fields from the model
        this.calculatedFields = _.chain(this.model.fields).where({calculated: true}).pluck('name').value();
    },

    /**
     * @inheritdoc
     */
    hasUnsavedChanges: function() {
        // keep track of the current values
        var currentNoEditFields = this.noEditFields;

        // if we need to ignore the calculated fields because they haven't been updated from the server and also ignore
        // the bundles collection field since we are going to check it below manually,
        this.noEditFields = this.noEditFields.concat(this.calculatedFields, ['bundles']);
        var hasUnSavedChanged = this._super('hasUnsavedChanges');

        var bundleCalculatedFields;
        var itemCalculatedFields = {};

        // if we don't have unsaved changes on the quote, check the bundles and their items
        if (hasUnSavedChanged === false) {
            var bundles = this.model.get('bundles');
            var changedFields = function(model) {
                return model.changedAttributes(model.getSynced());
            };
            var changedBundle;
            changedBundle = bundles.find(function(bundle) {
                var bundleChanged;

                if (_.isUndefined(bundleCalculatedFields)) {
                    bundleCalculatedFields = _.chain(bundle.fields).where({calculated: true}).pluck('name').value();
                }

                // VirtualCollection will return the collection as changed if something was added and save or canceled
                // just ignore it since, all the items are checked the models to see if they changed.
                bundleChanged = !_.isEmpty(
                    _.omit(changedFields(bundle), ['product_bundle_items'].concat(bundleCalculatedFields))
                );

                if (bundleChanged === false && bundle.has('product_bundle_items')) {
                    // the bundle hasn't changed, so lets check the items on it
                    var items = bundle.get('product_bundle_items');
                    var changedItem;
                    changedItem = items.find(function(item) {
                        // we have an new item that is not saved yet.
                        if (item.isNew()) {
                            return true;
                        }
                        if (_.isUndefined(itemCalculatedFields[item.module])) {
                            itemCalculatedFields[item.module] =
                                _.chain(item.fields).where({calculated: true}).pluck('name').value();
                        }
                        return !_.isEmpty(
                            _.omit(changedFields(item), itemCalculatedFields[item.module])
                        );
                    });

                    bundleChanged = !_.isUndefined(changedItem);
                }

                return bundleChanged;
            }, this);

            hasUnSavedChanged = !_.isUndefined(changedBundle);
        }

        this.noEditFields = currentNoEditFields;

        // return the value from the super
        return hasUnSavedChanged;
    }
})
