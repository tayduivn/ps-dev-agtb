/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

/**
 * Forecast Records View
 *
 * Events
 *
 * forecasts:worksheet:committed
 *  on: this.context
 *  by: commitForecast
 *  when: after a successful Forecast Commit
 */
({
    /**
     * The options from the initialize call
     */
    initOptions: undefined,

    /**
     * Overrides the Layout.initialize function and does not call the parent so we can defer initialization
     * until _onceInitSelectedUser is called
     *
     * @override
     */
    initialize: function(options) {
        // the parent is not called here so we make sure that nothing else renders until after we init the
        // the forecast module
        this.initOptions = options;

        // Check to make sure users have proper values in their sales_stage_won/_lost cfg values
        if(!this.checkSalesWonLost()) {
            // codeblock this sucka
            this.codeBlockForecasts();
        } else {
            // correct config exists, continue with syncInitData
            this.syncInitData();
        }
    },

    /**
     * Makes sure that Sales Stage Won/Lost values from the database Forecasts config settings
     * exist in the sales_stage_dom
     *
     * @returns {boolean}
     */
    checkSalesWonLost: function() {
        var cfg = app.metadata.getModule('Forecasts', 'config'),
            salesWonVals = cfg.sales_stage_won,
            salesLostVals = cfg.sales_stage_lost,
            salesWonLostVals = cfg.sales_stage_won.concat(cfg.sales_stage_lost),
            domVals = app.lang.getAppListStrings('sales_stage_dom'),
            forecastsOK = true;

        if(salesWonVals.length == 0 || salesLostVals.length == 0 || _.isEmpty(domVals)) {
            forecastsOK = false;
        } else {
            forecastsOK = _.every(salesWonLostVals, function(val) {
                return (val != '' && _.has(domVals, val));
            }, this);
        }

        return forecastsOK;
    },

    /**
     * Blocks forecasts from continuing to load
     */
    codeBlockForecasts: function() {
        var alert = app.alert.show('error_missing_stages', {
            level: 'error',
            autoClose: false,
            title: app.lang.get('LBL_FORECASTS_MISSING_STAGE_TITLE', "Forecasts") + ":",
            messages: [app.lang.get('LBL_FORECASTS_MISSING_SALES_STAGE_VALUES', "Forecasts")]
        });

        alert.getCloseSelector().on('click', function() {
            alert.getCloseSelector().off();
            app.router.navigate('#Home', {trigger: true});
        });
    },

    /**
     * Overrides loadData to defer it running until we call it in _onceInitSelectedUser
     *
     * @override
     */
    loadData: function() {
    },

    /**
     * {@inheritdoc}
     */
    bindDataChange: function() {
        // we need this here to track when the selectedTimeperiod changes and then also move it up to the context
        // so the recordlists can listen for it.
        if (!_.isUndefined(this.model)) {
            this.collection.on('reset', function() {
                // get the first model and set the last commit date
                var lastCommit = _.first(this.collection.models);
                var commitDate = undefined;
                if (lastCommit instanceof Backbone.Model && lastCommit.has('date_modified')) {
                    commitDate = lastCommit.get('date_modified');
                }
                this.context.set({'currentForecastCommitDate': commitDate});
            }, this);
            // since the selected user change on the context, update the model
            this.context.on('change:selectedUser', function(model, changed) {
                var update = {
                    'selectedUserId': changed.id,
                    'forecastType': app.utils.getForecastType(changed.isManager, changed.showOpps)
                }
                this.model.set(update);
            }, this);
            // since the selected timeperiod changes on the model, update the timeperiod
            this.model.on('change:selectedTimePeriod', function(model, changed) {
                this.context.set('selectedTimePeriod', changed);
            }, this);
            // if the model changes, run a fetch
            this.model.on('change', function() {
                this.collection.fetch();
            }, this);

            // listen on the context for a commit trigger
            this.context.on('forecasts:worksheet:commit', function(user, worksheet_type, forecast_totals) {
                this.commitForecast(user, worksheet_type, forecast_totals);
            }, this);

            this.context.on('button:settings_button:click', function() {
                this.openConfigDrawer();
            }, this);
        }
    },

    /**
     * Opens the Forecasts Config drawer
     */
    openConfigDrawer: function() {
        // open a drawer for the config layout, pass in our current config
        // in drawer is used in case user navigates to the config from Admin
        // and it isnt in a drawer, it redirects different if they save/cancel
        app.drawer.open({
            layout: 'config',
            context: {
                inDrawer: true
            }
        }, _.bind(function(hasChanged, data) {
            if(hasChanged && this.context) {
                // Now that we've reset the metadata with any new changes, let other models know
                this.context.trigger('forecasts:metadata:changed', data);
            }
        },this));
    },

    /**
     * Get the Forecast Init Data from the server
     *
     * @param {Object} options
     */
    syncInitData: function(options) {
        var callbacks,
            url;

        options = options || {};
        // custom success handler
        options.success = _.bind(function(model, data, options) {
            // Add Forecasts-specific stuff to the app.user object
            app.user.set(data.initData.userData);
            if (data.initData.forecasts_setup === 0) {
                // Immediately open the config drawer so user can set up config
                this.openConfigDrawer();
            } else {
                this.initForecastsModule(data, options);
            }
        }, this);

        // since we have not initialized the view yet, pull the model from the initOptions.context
        var model = this.initOptions.context.get('model');
        callbacks = app.data.getSyncCallbacks('read', model, options);
        this.trigger("data:sync:start", 'read', model, options);

        url = app.api.buildURL("Forecasts/init", null, null, options.params);
        app.api.call("read", url, null, callbacks);
    },

    /**
     * Process the Forecast Data
     *
     * @param {Object} data contains the data passed back from Forecasts/init endpoint
     * @param {Object} options
     */
    initForecastsModule: function(data, options) {
        var ctx = this.initOptions.context;
        // we watch for the first selectedUser change to actually init the Forecast Module case then we know we have
        // a proper selected user
        ctx.once('change:selectedUser', this._onceInitSelectedUser, this);

        // set items on the context from the initData payload
        ctx.set({
            currentForecastCommitDate: undefined,
            selectedTimePeriod: data.defaultSelections.timeperiod_id.id,
            selectedRanges: data.defaultSelections.ranges
        }, {silent: true});

        ctx.get('model').set({'selectedTimePeriod': data.defaultSelections.timeperiod_id.id}, {silent: true});

        // set the selected user to the context
        app.utils.getSelectedUsersReportees(app.user.toJSON(), ctx);
    },

    /**
     * Event handler for change:selectedUser
     * Triggered once when the user is set for the first time.  After setting user it calls
     * the init of the records layout
     *
     * @param {Backbone.Model} model the model from the change event
     * @param {String} change the updated selectedUser value from the change event
     * @private
     */
    _onceInitSelectedUser: function(model, change) {
        // init the recordlist view
        app.view.Layout.prototype.initialize.call(this, this.initOptions);

        // set the selected user and forecast type on the model
        this.model.set('selectedUserId', change.id, {silent: true});
        this.model.set('forecastType', app.utils.getForecastType(change.isManager, change.showOpps));
        // bind the collection sync to our custom sync
        this.collection.sync = _.bind(this.sync, this);

        // load the data
        app.view.Layout.prototype.loadData.call(this);
        // bind the data change
        this.bindDataChange();
        // render everything
        if (!this.disposed) this.render();
    },

    /**
     * Custom sync method used by this.collection
     *
     * @param {String} method
     * @param {Backbone.Model} model
     * @param {Object} options
     */
    sync: function(method, model, options) {
        var callbacks,
            url;

        options = options || {};

        options.params = options.params || {};

        var args_filter = [],
            filter = null;
        if (this.model.has('selectedTimePeriod')) {
            args_filter.push({"timeperiod_id": this.model.get('selectedTimePeriod')});
        }
        if (this.model.has('selectedUserId')) {
            args_filter.push({"user_id": this.model.get('selectedUserId')});
            args_filter.push({"forecast_type": this.model.get('forecastType')});
        }

        if (!_.isEmpty(args_filter)) {
            filter = {"filter": args_filter};
        }

        options.params.order_by = 'date_entered:DESC'
        options = app.data.parseOptionsForSync(method, model, options);

        // custom success handler
        options.success = _.bind(function(model, data, options) {
            this.collection.reset(data);
        }, this);

        callbacks = app.data.getSyncCallbacks(method, model, options);
        this.trigger("data:sync:start", method, model, options);

        url = app.api.buildURL("Forecasts/filter", null, null, options.params);
        app.api.call("create", url, filter, callbacks);
    },

    /**
     * Commit A Forecast
     *
     * @triggers forecasts:worksheet:committed
     * @param {Object} user
     * @param {String} worksheet_type
     * @param {Object} forecast_totals
     */
    commitForecast: function(user, worksheet_type, forecast_totals) {
        var forecast = new this.collection.model(),
            forecastType = app.utils.getForecastType(user.isManager, user.showOpps),
            forecastData = {},
            totalsProperty = 'case',
            likelyProperty = 'amount';

        if (forecastType == 'Rollup') {
            totalsProperty = 'adjusted';
            likelyProperty = 'likely_adjusted';
        }

        forecastData.best_case = forecast_totals['best_' + totalsProperty];
        forecastData.likely_case = forecast_totals[likelyProperty];
        forecastData.worst_case = forecast_totals['worst_' + totalsProperty];

        // we need a commit_type so we know what to do on the back end.
        forecastData.commit_type = worksheet_type;
        forecastData.timeperiod_id = forecast_totals.timeperiod_id || this.model.get('selectedTimePeriod');
        forecastData.forecast_type = forecastType;
        forecastData.amount = forecast_totals.amount || forecastData.likely_case;
        forecastData.opp_count = forecast_totals.included_opp_count;
        forecastData.closed_amount = forecast_totals.closed_amount;
        forecastData.closed_count = forecast_totals.closed_count;
        forecastData.pipeline_amount = forecast_totals.pipeline_amount || 0;
        forecastData.pipeline_opp_count = forecast_totals.pipeline_opp_count || 0;

        forecast.save(forecastData, { success: _.bind(function() {
            // we need to make sure we are not disposed, this handles any errors that could come from the router and window
            // alert events
            if (!this.disposed) {
                // Call sync again so commitLog has the full collection
                // method gets overridden and options just needs an
                this.collection.fetch();
                this.context.trigger("forecasts:worksheet:committed", worksheet_type, forecastData);
                app.alert.show('success', {
                    level: 'success',
                    autoClose: true,
                    title: app.lang.get("LBL_FORECASTS_WIZARD_SUCCESS_TITLE", "Forecasts") + ":",
                    messages: [app.lang.get("LBL_FORECASTS_WORKSHEET_COMMIT_SUCCESS", "Forecasts")]
                });
            }
        }, this), silent: true, alerts: { 'success': false }});
    }
})
