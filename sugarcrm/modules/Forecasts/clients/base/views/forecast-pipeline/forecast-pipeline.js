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
    plugins: ['Dashlet', 'Tooltip'],

    /**
     * Is the forecast Module setup??
     */
    forecastSetup: 0,

    /**
     * Is the user a forecast Admin? This is only used if forecasts is not setup
     */
    forecastAdmin: false,

    /**
     * The open state of the sidepanel
     */
    state: "open",

    /**
     * Visible state of the preview window
     */
    preview_open: false,

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

        if(!this.forecastSetup) {
            this.forecastsNotSetUpMsg = app.utils.getForecastNotSetUpMessage(this.forecastAdmin);
        }
    },

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
        if(this.forecastSetup) {
            app.api.call('GET', app.api.buildURL('TimePeriods/current'), null, {
                success: _.bind(function(o) {
                    this.settings.set({'selectedTimePeriod': o.id}, {silent: true});
                    this.layout.loadData();
                }, this),
                complete: view.options ? view.options.complete : null
            });
        }
        var currencySymbol = SUGAR.App.currency.getCurrencySymbol(SUGAR.App.currency.getBaseCurrencyId());
        this.chart = nv.models.funnelChart()
            .showTitle(false)
            .tooltips(true)
            .margin({top:0})
            .tooltipContent( function(key, x, y, e, graph) {
                return '<p>Stage: <b>' + key + '</b></p>' +
                    '<p>Amount: <b>' + y + '</b></p>' +
                    '<p>Percent: <b>' +  x + '%</b></p>';
            })
            .colorData('class', {step:2})
            .fmtValueLabel(function(d) {
                return d.label;
            });
    },

    bindDataChange: function() {
        this.settings.on('change', function(model) {
            // reload the chart
            // reload the chart
            if(this.state == 'open' && !this.preview_open) {
                this.loadData({});
            }
        }, this);

        app.events.on('preview:open', function() {
            this.preview_open = true;
        }, this);
        app.events.on('preview:close', function() {
            this.preview_open = false;
            if (this.chartLoaded) {
                if(this.chart && this.chart.update)
                    this.chart.update();
            }
        }, this);

        // FIXME this event should be listened on the `default` layout instead of the global context (SC-2398).
        app.controller.context.on('sidebar:state:changed', function(state) {
            this.state = state;
            if (this.chartLoaded && this.state === 'open' && !this.preview_open &&
                this.chart && _.isFunction(this.chart.update)) {
                this.chart.update();
            }
        }, this);
    },

    /**
     * {@inheritDoc}
     */
    _renderHtml: function() {
        this._super('_renderHtml');
        if(this.chart && !_.isEmpty(this.results)) {
            this.renderChart();
        }
    },

    renderChart: function() {
        if (this.state != 'open' || this.preview_open) {
            return;
        }

        if(this.disposed) {
            return;
        }

        // clear out the current chart before a re-render
        if (!_.isEmpty(this.chart)) {
            nv.utils.windowUnResize(this.chart.update);
            this.$("svg#" + this.cid).children().remove();
        }

        if (this.results.data && this.results.data.length > 0) {
            this.$('.nv-chart').toggleClass('hide', false);
            this.$('.block-footer').toggleClass('hide', true);

            d3.select('svg#' + this.cid)
                .datum(this.results)
                .transition().duration(500).call(this.chart);

            nv.utils.windowResize(this.chart.update);
            this.resizeOnPrint(this.chart);
            this.chartLoaded = true;
        } else {
            this.$('.nv-chart').toggleClass('hide', true);
            this.$('.block-footer').toggleClass('hide', false);
            this.chartLoaded = false;
        }
    },

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

    resizeOnPrint: function(chart) {

        var resizeChart = function(){
            chart.delay(0);
            chart.update();
            chart.delay(300);
        };

        if (window.matchMedia) {
            var mediaQueryList = window.matchMedia('print');
            mediaQueryList.addListener(function(mql) {
                if (mql.matches) {
                    resizeChart();
                }
            });
        } else if (window.attachEvent) {
          window.attachEvent("onbeforeprint", resizeChart);
        } else {
          window.onbeforeprint = resizeChart;
        }

        window.onafterprint = resizeChart;
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
     * @inheritDoc
     */
    unbind: function() {
        // FIXME the listener should be on the `default` layout instead of the global context (SC-2398).
        app.controller.context.off(null, null, this);
        this._super('unbind');
    }
})
