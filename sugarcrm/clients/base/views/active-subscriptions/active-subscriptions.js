//FILE SUGARCRM flav=ent ONLY
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
 * @class View.Views.Base.ActiveSubscriptionsView
 * @alias SUGAR.App.view.views.BaseActiveSubscriptionsView
 * @extends View.View
 */
({

    plugins: ['Dashlet'],

    /**
     * The module name to show active subscriptions for.
     *
     * @property {string}
     */
    baseModule: null,

    /**
     * The model to show active subscriptions for.
     *
     * @property {Object}
     */
    baseModel: null,

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.module = 'RevenueLineItems';
        this.baseModule = 'Accounts';
        this._getBaseModel();
    },

    /**
     * Set up collection when init dashlet.
     *
     * @param {string} viewName Current view
     */
    initDashlet: function(viewName) {
        this._mode = viewName;
        this._initCollection();
    },

    /**
     * Get base model from parent context.
     *
     * @private
     */
    _getBaseModel: function() {
        var baseModule = this.context.get('module');
        var currContext = this.context;

        if (baseModule !== this.baseModule) {
            return;
        }

        while (currContext) {
            var contextModel = currContext.get('rowModel') || currContext.get('model');

            if (contextModel && contextModel.get('_module') === baseModule) {
                this.baseModel = contextModel;
                break;
            }

            currContext = currContext.parent;
        }
    },

    /**
     * Initialize collection.
     *
     * @private
     */
    _initCollection: function() {
        if (!this.baseModel) {
            return;
        }
        var today = app.date().formatServer(true);
        var filter = [
            {
                'account_id': {
                    '$equals': this.baseModel.get('id')
                }
            },
            {
                'opportunities.sales_status': {
                    '$equals': 'Closed Won'
                }
            },
            {
                'sales_stage': {
                    '$equals': 'Closed Won'
                }
            },
            {
                'service_duration_value': {
                    '$gt': 0
                }
            },
            {
                'service_start_date': {
                    '$lte': today
                }
            },
            {
                'service_end_date': {
                    '$gte': today
                }
            }
        ];
        var options = {
            'fields': this.meta.fields || [],
            'filter': filter,
            'limit': app.config.maxRecordFetchSize || 1000,
            'params': {
                'order_by': 'service_start_date',
            },
            'success': _.bind(function() {
                if (this.disposed) {
                    return;
                }
                this.render();
            }, this)
        };
        this.collection = app.data.createBeanCollection(this.module, null, options);
        this.collection.fieldsMeta = {
            'total_amount': {
                'name': 'total_amount',
                'type': 'currency',
                'convertToBase': true,
                'currency_field': 'currency_id',
                'base_rate_field': 'base_rate'
            }
        };
    },

    /**
     * Load active subscriptions.
     *
     * @param {Object} options Call options
     */
    loadData: function(options) {
        if (this._mode === 'config') {
            return;
        }
        this.collection.fetch(options);
    }
})
