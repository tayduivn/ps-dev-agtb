/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
({
    results: {},
    chart: {},
    plugins: ['Dashlet', 'Chart', 'Tooltip'],

    /**
     * Is the forecast Module setup??
     */
    forecastSetup: 0,

    /**
     * Is the user a forecast Admin? This is only used if forecasts is not setup
     */
    forecastAdmin: false,

    /**
     * Holds the forecast isn't set up message if Forecasts hasn't been set up yet
     */
    forecastsNotSetUpMsg: undefined,

    /**
     * Track if current user is manager.
     */
    isManager: false,

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this.isManager = app.user.get('is_manager');
        this._initPlugins();
        this._super('initialize', [options]);

        // check to make sure that forecast is configured
        this.forecastSetup = app.metadata.getModule('Forecasts', 'config').is_setup;
        this.forecastAdmin = (_.isUndefined(app.user.getAcls()['Forecasts'].admin));

        if (!this.forecastSetup) {
            this.forecastsNotSetUpMsg = app.utils.getForecastNotSetUpMessage(this.forecastAdmin);
        }
    },

    /**
     * {@inheritDoc}
     */
    initDashlet: function(view) {
        if (!this.isManager && this.meta.config) {
            // FIXME: Dashlet's config page is rendered from meta.panels directly.
            // See the "dashletconfiguration-edit.hbs" file.
            this.meta.panels = _.chain(this.meta.panels).filter(function(panel) {
                panel.fields = _.without(panel.fields, _.findWhere(panel.fields, {name: 'visibility'}));
                return panel;
            }).value();
        }
        // get the current timeperiod
        if (this.forecastSetup) {
            app.api.call('GET', app.api.buildURL('TimePeriods/current'), null, {
                success: _.bind(function(currentTP) {
                    this.settings.set({'selectedTimePeriod': currentTP.id}, {silent: true});
                    this.layout.loadData();
                }, this),
                error: _.bind(function() {
                    // Needed to catch the 404 in case there isnt a current timeperiod
                }, this),
                complete: view.options ? view.options.complete : null
            });
        }
        var currencySymbol = SUGAR.App.currency.getCurrencySymbol(SUGAR.App.currency.getBaseCurrencyId());
        this.chart = nv.models.funnelChart()
            .showTitle(false)
            .tooltips(true)
            .margin({top: 0})
            .tooltipContent(function(key, x, y, e, graph) {
                return '<p>Stage: <b>' + key + '</b></p>' +
                    '<p>Amount: <b>' + y + '</b></p>' +
                    '<p>Percent: <b>' + x + '%</b></p>';
            })
            .colorData('class', {step: 2})
            .fmtValueLabel(function(d) {
                return d.label;
            });
    },

    /**
     * Initialize plugins.
     * Only manager can toggle visibility.
     *
     * @return {View.Views.BaseForecastPipeline} Instance of this view.
     * @protected
     */
    _initPlugins: function() {
        if (this.isManager) {
            this.plugins = _.union(this.plugins, [
                'ToggleVisibility'
            ]);
        }
        return this;
    },

    /**
     * {@inheritDoc}
     */
    bindDataChange: function() {
        this.settings.on('change', function(model) {
            // reload the chart
            if (this.$el && this.$el.is(':visible')) {
                this.loadData({});
            }
        }, this);
    },

    /**
     * Generic method to render chart with check for visibility and data.
     * Called by _renderHtml and loadData.
     */
    renderChart: function() {
        if (!this.isChartReady()) {
            return;
        }
        // Clear out the current chart before a re-render
        this.$('svg#' + this.cid).children().remove();

        d3.select('svg#' + this.cid)
            .datum(this.results)
            .transition().duration(500)
            .call(this.chart);

        this.chart_loaded = _.isFunction(this.chart.update);
        this.displayNoData(!this.chart_loaded);
    },


    hasChartData: function() {
        return !_.isEmpty(this.results) && this.results.data && this.results.data.length > 0;
    },

    /**
     * @inheritDoc
     */
    loadData: function(options) {
        var timePeriod = this.settings.get('selectedTimePeriod');
        if (!timePeriod) {
            return;
        }
//BEGIN SUGARCRM flav=pro && flav!=ent ONLY
        var url_base = 'Opportunities/chart/pipeline';
//END SUGARCRM flav=pro && flav!=ent ONLY
//BEGIN SUGARCRM flav=ent ONLY
        var url_base = 'RevenueLineItems/chart/pipeline';
//END SUGARCRM flav=ent ONLY
        if (this.settings.has('selectedTimePeriod')) {
            url_base += '/' + timePeriod;
            if (this.isManager) {
                url_base += '/' + this.getVisibility();
            }
            var url = app.api.buildURL(url_base);
            app.api.call('GET', url, null, {
                success: _.bind(function(o) {
                    this.results = {};
                    this.results = o;
                    this.renderChart();
                }, this),
                error: _.bind(function(o) {
                    this.results = {};
                    this.renderChart();
                }, this),
                complete: options ? options.complete : null
            });
        }
    },

    /**
     * @inheritDoc
     */
    unbind: function() {
        this.settings.off('change');
        this._super('unbind');
    }
})
