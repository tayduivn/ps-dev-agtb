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
        app.view.Layout.prototype.initialize.call(this, options);
    },

    loadData : function() {
        if(_.isEmpty(this.initOptions)) {
            this.syncInitData();
        } else {
            app.view.Layout.prototype.loadData.call(this);
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

        callbacks = app.data.getSyncCallbacks('read', this.model, options);
        this.trigger("data:sync:start", 'read', this.model, options);

        url = app.api.buildURL("Forecasts/init", null, null, options.params);
        app.api.call("read", url, null, callbacks);
    },

    initForecastsModule: function(data, options) {
        this.context.set('selectedUser', app.user.attributes);

        app.view.Layout.prototype.loadData.call(this);
    }
})
