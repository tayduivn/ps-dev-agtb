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

({
    initOptions: {},

    initialize: function(options) {

        this.initOptions = options;
        this.syncInitData();
    },

    // overwrite load data, we will call this later
    loadData: function() {
    },

    bindDataChange: function() {
        // we need this here to track when the selectedTimeperiod changes and then also move it up to the context
        // so the recordlists can listen for it.
        if (!_.isUndefined(this.model)) {
            this.collection.on('reset', function() {
                // get the first model and set the last commit date
                var lastCommit = _.first(this.collection.models);
                this.context.set({'currentForecastCommitDate': lastCommit.get('date_entered')});
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
        }
    },

    syncInitData: function(options) {
        var callbacks,
            url;

        options = options || {};
        // custom success handler
        options.success = _.bind(function(model, data, options) {
            // Add Forecasts-specific stuff to the app.user object
            app.user.set(data.initData.userData);
            if (data.initData.forecasts_setup === 0) {
                window.location.hash = "#Forecasts/layout/config";
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

    initForecastsModule: function(data, options) {
        console.log('init_forecast_records_layout');
        var ctx = this.initOptions.context;
        ctx.once('change:selectedUser', function(model, change) {
            console.log('init_forecast change:selecteduser');
            // init the recordlist view
            app.view.Layout.prototype.initialize.call(this, this.initOptions);

            this.model.set('selectedUserId', change.id, {silent: true});
            this.model.set('forecastType', app.utils.getForecastType(change.isManager, change.showOpps));
            this.collection.sync = _.bind(this.sync, this);

            // load the data
            app.view.Layout.prototype.loadData.call(this);
            // bind the data change
            this.bindDataChange();
            // render everything
            if (!this.disposed) this.render();
        }, this);

        // set the selectedTimePeriod
        // set hte currentForecastDate to the time the page is inited, this will be updated on the page actually loads
        ctx.set({'currentForecastCommitDate': new Date().toISOString()});
        ctx.set({'selectedTimePeriod': data.defaultSelections.timeperiod_id.id}, {silent: true});
        ctx.get('model').set({'selectedTimePeriod': data.defaultSelections.timeperiod_id.id}, {silent: true});
        app.utils.getSelectedUsersReportees(app.user.toJSON(), ctx);
    },

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
            // Call sync again so commitLog has the full collection
            // method gets overridden and options just needs an
            this.collection.fetch();
            this.context.trigger("forecasts:worksheet:committed", worksheet_type, forecastData);
        }, this), silent: true, alerts: { 'success': false }});
    }
})
