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

    // overwrite load data, we will call this from above
    loadData: function() {},

    bindDataChange: function() {
        // we need this here to track when the selectedTimeperiod changes and then also move it up to the context
        // so the recordlists can listen for it.
        if (!_.isUndefined(this.model)) {
            this.model.on('change:selectedTimePeriod', function(model, changed) {
                this.context.set('selectedTimePeriod', changed);
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
            // load the data
            app.view.Layout.prototype.loadData.call(this);
            // bind the data change
            this.bindDataChange();
            // render everything
            if (!this.disposed) this.render();
        }, this);

        // skip the fetch on this context as we don't need it from this layout
        ctx.set('skipFetch', true);
        // set the selectedTimePeriod
        ctx.set({'selectedTimePeriod': data.defaultSelections.timeperiod_id.id}, {silent: true});
        ctx.get('model').set({'selectedTimePeriod': data.defaultSelections.timeperiod_id.id}, {silent: true});
        app.utils.getSelectedUsersReportees(app.user.toJSON(), ctx);
    }
})
