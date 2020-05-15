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

    overallSubscriptionStartDate: 0,

    overallSubscriptionEndDate: 0,

    overallDaysDifference: 0,

    endDate: '',

    expiryComingSoon: false,

    /**
     * Flag indicating Purchases module is enabled.
     *
     * @property {bool}
     */
    purchasesModule: false,

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.module = 'Purchases';
        this.moduleName = {'module_name': app.lang.getModuleName(this.module, {'plural': true})};
        this.baseModule = 'Accounts';
        this._getBaseModel();
        if (!_.isUndefined(app.metadata.getModule('Purchases'))) {
            this.purchasesModule = true;
        }
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
        if (!this.baseModel || !this.purchasesModule) {
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
                'service': {
                    '$equals': 1,
                },
            },
            {
                'start_date': {
                    '$lte': today
                }
            },
            {
                'end_date': {
                    '$gte': today
                }
            }
        ];
        var options = {
            'fields': this.dashletConfig.fields || [],
            'filter': filter,
            'limit': app.config.maxRecordFetchSize || 1000,
            'success': _.bind(function() {
                if (this.disposed) {
                    return;
                }
                var self = this;
                // Render here only when the model empty, else render after the bulk call is resolved.
                if (!this.collection.models.length) {
                    this.render();
                }
                _.each(this.collection.models, function(model) {
                    // add 1 day to display remaining time correctly
                    var nextDate = app.date(model.get('end_date')).add('1', 'day');
                    model.set('service_remaining_time', nextDate.fromNow());
                    collections = model.get('pli_collection');
                    // create the payload
                    var bulkSaveRequests = self._createBulkCollectionsPayload(collections);
                    // send the payload
                    self._sendBulkCollectionsUpdate(bulkSaveRequests);
                });
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
        if (this._mode === 'config' || !this.purchasesModule) {
            return;
        }
        this.collection.fetch(options);
    },

    /**
     * Utility method to create the payload that will be sent to the server via the bulk api call
     * to fetch the related PLIs for a purchase
     *
     * @param {Array} collections
     * @private
     */
    _createBulkCollectionsPayload: function(collections) {
        // loop over all the collections and create the requests
        var bulkSaveRequests = [];
        var url;
        collections.each(function(element) {
            // if the element is new, don't try and save it
            if (!element.isNew()) {
                // create the update url
                url = app.api.buildURL(element.module, 'read', {
                    id: element.get('id')
                });

                // get request on each PLI
                bulkSaveRequests.push({
                    // app.api.buildURL() in app.api.call() calls the rest endpoint with the following request
                    // remove rest from the url here
                    url: url.substr(4),
                    method: 'GET',
                });
            }
        });

        return bulkSaveRequests;
    },

    /**
     * Send the payload via the bulk api
     *
     * @param {Array} bulkSaveRequests
     * @private
     */
    _sendBulkCollectionsUpdate: function(bulkSaveRequests) {
        if (!_.isEmpty(bulkSaveRequests)) {
            app.api.call(
                'create',
                app.api.buildURL(null, 'bulk'),
                {
                    requests: bulkSaveRequests
                },
                {
                    success: _.bind(this._onBulkCollectionsUpdateSuccess, this)
                }
            );
        }
    },

    /**
     * Update the bundles when the results from the bulk api call
     *
     * @param {Array} bulkResponses
     * @private
     */
    _onBulkCollectionsUpdateSuccess: function(bulkResponses) {
        var purchaseId = _.first(bulkResponses).contents.purchase_id;
        var model = _.first(this.collection.models.filter(function(ele) {
            return ele.id === purchaseId;
        }));
        var collections = model.get('pli_collection');
        var element;
        var quantity = 0;
        var totalAmount = 0;

        _.each(bulkResponses, function(record) {
            element = collections.get(record.contents.id);
            if (element) {
                var startDate = app.date(record.contents.service_start_date);
                var endDate = app.date(record.contents.service_end_date);
                var today = app.date();
                if (startDate <= today && endDate >= today) {
                    quantity += record.contents.quantity;
                    totalAmount += parseInt(record.contents.total_amount, 10);
                }
            }
        }, this);
        model.set('quantity', quantity);
        model.set('total_amount', totalAmount);
        this._caseComparator();
        this._daysDifferenceCalculator();
        this.render();
    },

    /**
     * Calculates the upper and lower bounds for the timeline Graph calculating the earliest
     * Start Date and End Date for all the records.
     */
    _caseComparator: function() {
        if (this.collection) {
            var daysPast = moment('1970-01-01');
            var min = Number.MAX_VALUE;
            var max = 0;
            var start;
            var end;
            var modelArray = this.collection.models;
            modelArray.forEach(function(model) {
                start = model.get('start_date');
                start = this.moment(start);
                start = start.diff(daysPast, 'days');
                end = model.get('end_date');
                end = this.moment(end);
                end = end.diff(daysPast, 'days');
                if (max < end) {
                    max = end;
                }
                if (min > start) {
                    min = start;
                }
            });
            this.overallSubscriptionEndDate = max;
            this.overallSubscriptionStartDate = min;
        }
    },

    /**
     * Calculates the width for the graph by adjusting in to the 60% width
     * and sets width for the subscription time past and subscription time left
     * to fit into 60% width.
     */
    _daysDifferenceCalculator: function() {
        var daysPast = moment('1970-01-01');
        var today = moment();
        if (this.collection) {
            var overallSubscriptionStartDate = this.overallSubscriptionStartDate;
            var overallDaysDifference = this.overallSubscriptionEndDate - overallSubscriptionStartDate;
            var start = null;
            var end = null;
            var startDate = null;
            var endDate = null;
            var activeTimelineWidth = null;
            var activePastTimelineWidth = null;
            var timelineOffset = 40;
            today = today.diff(daysPast, 'days');

            _.each(this.collection.models, function(model) {
                start = model.get('start_date');
                start = this.moment(start);
                start = start.diff(daysPast, 'days');
                startDate = ((start - overallSubscriptionStartDate) / overallDaysDifference).toFixed(2) * 100;

                end = model.get('end_date');
                end = this.moment(end);
                this.endDate = end;
                end = end.diff(daysPast, 'days');
                endDate = ((end - overallSubscriptionStartDate) / overallDaysDifference).toFixed(2) * 100;

                activeTimelineWidth = ((end - start) / overallDaysDifference) * 60;
                timelineOffset = timelineOffset + startDate * 0.6;
                activeTimelineWidth = (activeTimelineWidth + timelineOffset) > 100 ? (100 - timelineOffset)
                    : activeTimelineWidth;
                activePastTimelineWidth = ((today - start) / (end - start)) * 100;
                activePastTimelineWidth = activePastTimelineWidth >= 100 ? activePastTimelineWidth - 1
                    : activePastTimelineWidth;
                this.expiryComingSoon = (activePastTimelineWidth) >= 90 ? true : false;
                timelineOffset = isNaN(timelineOffset) ? 40 : timelineOffset;
                activeTimelineWidth = isNaN(activeTimelineWidth) ? 60 : activeTimelineWidth;
                activePastTimelineWidth = isNaN(activePastTimelineWidth) ? 99 : activePastTimelineWidth;
                activeTimelineWidth = (activeTimelineWidth === 0) ? 100 - activePastTimelineWidth : activeTimelineWidth;
                model.set({
                    startDate: _.first(app.date(model.get('start_date')).formatUser().split(' ')),
                    endDate: _.first(app.date(model.get('end_date')).formatUser().split(' ')),
                    expiration: this.endDate.fromNow(),
                    timelineOffset: timelineOffset,
                    subscriptionValidityActive: activeTimelineWidth.toFixed(2),
                    subscriptionActiveWidth: activePastTimelineWidth.toFixed(2),
                    expiryComingSoon: this.expiryComingSoon
                });
                timelineOffset = 40;
            });
        }
    },
})
