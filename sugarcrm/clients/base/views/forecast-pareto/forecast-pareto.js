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
 * Dashlet that displays a chart
 */
({
    plugins: ['Dashlet'],

    values: new Backbone.Model(),

    className: 'forecasts-chart-wrapper',

    displayTimeperiodPivot: true,

    /**
     * Hold the initOptions if we have to call the Forecast/init end point cause we are not on Forecasts
     */
    initOptions: null,

    events: {
        'click button.btn': 'handleTypeButtonClick'
    },

    /**
     * {@inheritdoc}
     */
    initialize: function(options) {
        this.values.clear({silent: true});
        // if the parent exists, use it, otherwise use the main context
        this.initOptions = options;

        this.forecastConfig = app.metadata.getModule('Forecasts', 'config');
        this.isForecastSetup = this.forecastConfig.is_setup;
        this.forecastsConfigOK = app.utils.checkForecastConfig();

        if (this.isForecastSetup && this.forecastsConfigOK) {
            this.initOptions.meta.template = undefined;
            this.initComponent();
        } else {
            // set the no access template
            this.initOptions.meta.template = 'forecast-pareto.no-access';
            app.view.View.prototype.initialize.call(this, this.initOptions);
        }
    },

    /**
     * Handle the call to the Forecast/init call so we can get the defaults back
     */
    initComponent: function() {
        app.api.call('GET', app.api.buildURL('Forecasts/init'), null, {
            success: _.bind(function(o) {
                app.view.View.prototype.initialize.call(this, this.initOptions);
                this.values.module = 'Forecasts';
                this.isManager = o.initData.userData.isManager;
                this.values.set({
                    user_id: app.user.get('id'),
                    display_manager: false, // we always show the rep view by default
                    selectedTimePeriod: o.defaultSelections.timeperiod_id.id,
                    timeperiod_id: o.defaultSelections.timeperiod_id.id,
                    timeperiod_label: o.defaultSelections.timeperiod_id.label,
                    dataset: o.defaultSelections.dataset,
                    group_by: o.defaultSelections.group_by,
                    ranges: o.defaultSelections.ranges
                });
                this.bindDataChange();
                this.render();
            }, this),
            complete: this.initOptions ? this.initOptions.complete : null
        });
    },

    /**
     * Specific code to run after a dashlet Init Code has ran
     *
     */
    initDashlet: function() {
        var fieldOptions,
            cfg = app.metadata.getModule('Forecasts', 'config');
        fieldOptions = app.lang.getAppListStrings(this.dashletConfig.dataset.options);
        this.dashletConfig.dataset.options = {};

        if (cfg.show_worksheet_worst) {
            this.dashletConfig.dataset.options['worst'] = fieldOptions['worst'];
        }

        if (cfg.show_worksheet_likely) {
            this.dashletConfig.dataset.options['likely'] = fieldOptions['likely'];
        }

        if (cfg.show_worksheet_best) {
            this.dashletConfig.dataset.options['best'] = fieldOptions['best'];
        }
    },

    /**
     * When loadData is called, find the paretoChart field, if it exist, then have it render the chart
     *
     * @override
     */
    loadData: function(options) {
        var f = this.getField('paretoChart');

        if (!_.isUndefined(f)) {
            f.renderChart(options);
        }
    },

    /**
     * Called after _render
     */
    toggleRepOptionsVisibility: function() {
        if (this.values.get('display_manager') === true) {
            this.$el.find('div.groupByOptions').addClass('hide');
        } else {
            this.$el.find('div.groupByOptions').removeClass('hide');
        }

        if (this.isManager) {
            this.$el.find('#' + this.cid + '_mgr_toggle').toggleClass('span3', 'span6');
        }
    },

    /**
     * Handle when the type button is clicked
     * @param e
     */
    handleTypeButtonClick: function(e) {
        var $el = $(e.currentTarget),
            displayType = $el.data('type');

        this.values.set({display_manager: (displayType == 'team')});
    },

    /**
     * {@inheritdoc}
     */
    bindDataChange: function() {
        // on the off chance that the init has not run yet.
        var meta = this.meta || this.initOptions.meta;
        if (meta.config) {
            return;
        }

        // if we don't have a context, this shouldn't run yet.
        if (_.isUndefined(this.context)) {
            return;
        }

        if (this.isForecastSetup && this.forecastsConfigOK) {
            this.values.on('change:display_manager', this.toggleRepOptionsVisibility, this);
            this.values.on('change:selectedTimePeriod', function(context, timeperiod) {
                this.values.set({timeperiod_id: timeperiod});
            }, this);
        }
    },

    /**
     * {@inheritdoc}
     * Clean up!
     */
    unbindData: function() {
        var ctx = this.context.parent;
        if (ctx) {
            ctx.off(null, null, this);
        }
        if (this.context) this.context.off(null, null, this);
        if (this.values) this.values.off(null, null, this);
        app.view.View.prototype.unbindData.call(this);
    }
})
