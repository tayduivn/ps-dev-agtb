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
 * @class View.Views.Base.Quotes.CreateView
 * @alias SUGAR.App.view.views.BaseQuotesCreateView
 * @extends View.Views.Base.CreateView
 */
({
    extendsFrom: 'CreateView',

    /**
     * Holds the ProductBundles/Products/ProductBundleNotes fields meta for different views
     */
    moduleFieldsMeta: undefined,

    /**
     * Field map for where Opp/RLI fields (values) should map to Quote fields (keys)
     */
    convertToQuoteFieldMap: {
        Opportunities: {
            opportunity_id: 'id',
            opportunity_name: 'name'
        },
        RevenueLineItems: {
            name: 'name',
            opportunity_id: 'opportunity_id',
            opportunity_name: 'opportunity_name'
        },
        default: {
            billing_accounts: 'accounts',
            shipping_accounts: 'accounts',
            billing_account_id: 'account_id',
            billing_account_name: 'account_name',
            shipping_account_id: 'account_id',
            shipping_account_name: 'account_name'
        }
    },

    /**
     * A list of field names to pull from the Account model to the Quote model
     */
    acctToQuoteConvertFields: [
        'billing_address_city',
        'billing_address_country',
        'billing_address_postalcode',
        'billing_address_state',
        'billing_address_street',
        'shipping_address_city',
        'shipping_address_country',
        'shipping_address_postalcode',
        'shipping_address_state',
        'shipping_address_street'
    ],

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this.plugins = _.union(this.plugins || [], ['QuotesViewSaveHelper']);

        if (options.context.get('convert')) {
            this._prepopulateQuoteWithOpp(options);
        }

        this._super('initialize', [options]);

        this.moduleFieldsMeta = {};

        this._buildMeta('ProductBundleNotes', 'quote-data-group-list');
        this._buildMeta('ProductBundles', 'quote-data-group-header');
        this._buildMeta('Products', 'quote-data-group-list');

        // gets the name of any field where calculated is true
        this.calculatedFields = _.chain(this.model.fields)
            .where({calculated: true})
            .pluck('name')
            .value();
    },

    /**
     * Prepopulates the Quote context model with with Opp/Account ID
     *
     * @param {Object} options The initialize options Object
     * @protected
     */
    _prepopulateQuoteWithOpp: function(options) {
        var parentModel = options.context.get('parentModel');
        var ctxModel = options.context.get('model');
        var quoteData = {};
        var fieldMap;

        if (ctxModel && parentModel) {
            // create a field map from the default fields and module-specific fields
            fieldMap = _.extend(
                {},
                this.convertToQuoteFieldMap.default,
                this.convertToQuoteFieldMap[parentModel.module]
            );
            _.each(fieldMap, function(otherModuleField, quoteField) {
                quoteData[quoteField] = parentModel.get(otherModuleField);
            }, this);

            app.api.call('read', app.api.buildURL('Accounts/' + parentModel.get('account_id')), null, {
                success: _.bind(this._setAccountInfo, this)
            });
            ctxModel.set(quoteData);
        }
    },

    /**
     * Sets the related Account info on the Quote bean
     *
     * @param {Object} accountInfoData The Account info returned from the Accounts/:id endpoint
     * @protected
     */
    _setAccountInfo: function(accountInfoData) {
        var acctData = {};

        _.each(this.acctToQuoteConvertFields, function(fieldName) {
            acctData[fieldName] = accountInfoData[fieldName];
        }, this);

        this.model.set(acctData);
    },

    /**
     * Builds the `this.moduleFieldsMeta` object
     *
     * @param {string} moduleName The module name to get meta for
     * @param {string} viewName The view name from the module to get view defs for
     * @private
     */
    _buildMeta: function(moduleName, viewName) {
        var viewMeta;
        var modMeta;
        var metaFields = {};
        var modMetaField;

        modMeta = app.metadata.getModule(moduleName);
        viewMeta = app.metadata.getView(moduleName, viewName);

        _.each(viewMeta.panels, function(panel) {
            _.each(panel.fields, function(field) {
                modMetaField = modMeta.fields[field.name];
                metaFields[field.name] = _.extend({}, modMetaField, field);
            }, this);
        }, this);

        this.moduleFieldsMeta[moduleName] = metaFields;
    },

    /**
     * Overriding so after the Quote model validates,
     * all the bundles and their models validate as well
     *
     * @inheritdoc
     */
    validateModelWaterfall: function(callback) {
        // validate the main Quotes model
        this.model.doValidate(this.getFields(this.module), _.bind(function(isValid) {
            // then validate all the bundle models
            this.validateModels(isValid, callback, true);
        }, this));
    },

    /**
     * Validates the models in the Quote's ProductBundles
     *
     * @param {boolean} isValid If the parent Quote model is valid or not
     * @param {Function} callback The callback function to call after validation
     * @param {undefined|boolean} [fromCreateView] If this function is being called from Create view or not
     */
    validateModels: function(isValid, callback, fromCreateView) {
        fromCreateView = fromCreateView || false;

        var returnCt = 0;
        var totalItemsToValidate = 0;
        var bundles = this.model.get('bundles');
        var productBundleItems;

        if (bundles && bundles.length) {

            //Check to see if we have only the default group
            if (bundles.length === 1 && bundles.models.length === 1) {
                productBundleItems = bundles.models[0].get('product_bundle_items');
                //check to see if that group is empty, if so, return the valid status of the parent.
                if (productBundleItems.length === 0) {
                    if (fromCreateView) {
                        // the create waterfall wants the opposite of if this is validated
                        callback(!isValid);
                    } else {
                        // this view wants if the models are valid or not
                        callback(isValid);
                    }
                }
            }

            totalItemsToValidate += bundles.length;

            // get the count of items
            totalItemsToValidate = _.reduce(bundles.models, function(memo, bundle) {
                return memo + bundle.get('product_bundle_items').length;
            }, totalItemsToValidate);

            this.hasValidModels = isValid;

            // loop through each ProductBundles bean
            _.each(bundles.models, function(bundleModel) {
                // get the bundle items for this bundle to validate later
                productBundleItems = bundleModel.get('product_bundle_items');

                // call validate on the ProductBundle model (if group name were required or some other field)
                bundleModel.doValidate(this.moduleFieldsMeta[bundleModel.module], _.bind(function(isValid) {
                    // increment the validate count
                    returnCt++;
                    if (this.hasValidModels && !isValid) {
                        // hasValidModels was true, but a model returned false from validation
                        this.hasValidModels = isValid;
                    }
                    // loop through each product_bundle_items Products/ProductBundleNotes bean
                    _.each(productBundleItems.models, function(pbItemModel) {
                        // call validate on the Product/ProductBundleNote model
                        pbItemModel.doValidate(this.moduleFieldsMeta[pbItemModel.module], _.bind(function(isValid) {
                            // increment the validate count
                            returnCt++;
                            if (this.hasValidModels && !isValid) {
                                // hasValidModels was true, but a model returned false from validation
                                this.hasValidModels = isValid;
                            }

                            if (returnCt === totalItemsToValidate) {
                                // if we've validated the correct number of models, call the callback fn
                                if (fromCreateView) {
                                    // the create waterfall wants the opposite of if this is validated
                                    callback(!this.hasValidModels);
                                } else {
                                    // this view wants if the models are valid or not
                                    callback(this.hasValidModels);
                                }
                            }
                        }, this));
                    }, this);
                }, this));
            }, this);
        } else {
            // if there are no bundles to validate then just return
            // if the original model was valid
            if (fromCreateView) {
                //but opposite because that is what the waterfall expects
                callback(!isValid);
            } else {
                callback(isValid);
            }
        }
    },

    /**
     * @inheritdoc
     */
    saveModel: function(success, error) {
        /*var bundles = this.model.get('bundles');
        // unsetting _notSaved during CreateView so the it wont trip isNew() after the save
        bundles.each(function(bundle) {
            // bundle = ProductBundle model
            var items = bundle.get('product_bundle_items');
            items.each(function(item) {
                // item = QLI/Note model
                item.unset('_notSaved');
            }, this);
            bundle.unset('_notSaved');
        }, this);*/

        this._super('saveModel', [success, error]);
    },

    /**
     * Overriding to make the router go back to previous view, not Quotes module list
     *
     * @inheritdoc
     * @override
     */
    cancel: function() {
        //Clear unsaved changes on cancel.
        app.events.trigger('create:model:changed', false);
        this.$el.off();

        app.router.goBack();
    },

    /**
     * @inheritdoc
     */
    hasUnsavedChanges: function() {
        return this.hasUnsavedQuoteChanges();
    }
})
