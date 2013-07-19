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

    /**
     * Should we display the timeperiod Pivot options
     */
    displayTimeperiodPivot: true,

    /**
     * Should we dynamicly update the chart
     */
    dynamicUpdate: false,

    /**
     * Are we on the home page or not?
     */
    isOnHomePage: true,

    /**
     * When on a Record view this are fields we should listen to changes in
     */
    validChangedFields: ['amount', 'likely_case', 'best_case', 'worst_case',
        'date_closed', 'date_closed_timestamp', 'probability', 'commit_stage', 'sales_stage'],

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
        if (this.initOptions.meta.config) {
            app.view.View.prototype.initialize.call(this, this.initOptions);
        } else {
            app.api.call('GET', app.api.buildURL('Forecasts/init'), null, {
                success: _.bind(function(o) {
                    app.view.View.prototype.initialize.call(this, this.initOptions);
                    this.values.module = 'Forecasts';
                    this.isManager = o.initData.userData.isManager;
                    this.displayTimeperiodPivot = (this.context.get('module') === "Home");
    
                    var defaultOptions = {
                        user_id: app.user.get('id'),
                        display_manager: false, // we always show the rep view by default
                        selectedTimePeriod: o.defaultSelections.timeperiod_id.id,
                        timeperiod_id: o.defaultSelections.timeperiod_id.id,
                        timeperiod_label: o.defaultSelections.timeperiod_id.label,
                        dataset: o.defaultSelections.dataset,
                        group_by: o.defaultSelections.group_by,
                        ranges: _.keys(app.lang.getAppListStrings(this.forecastConfig.buckets_dom))
                    }
    
                    if (!this.displayTimeperiodPivot) {
                        this.isOnHomePage = false;
                        defaultOptions.timeperiod_id = this.model.get('date_closed_timestamp');
                        var mdlAssignedUserId = this.model.original_assigned_user_id || this.model.get("assigned_user_id");
                        this.dynamicUpdate = (mdlAssignedUserId == app.user.get('id'));
                    }
    
                    this.values.set(defaultOptions);
                    this.bindDataChange();
                    this.render();
                }, this),
                complete: this.initOptions ? this.initOptions.complete : null
            });
        }
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
            this.on('render', function() {
                var f = this.getField('paretoChart'),
                    dt = this.layout.getComponent('dashlet-toolbar');

                // if we have a dashlet-toolbar, then make it do the refresh icon while the chart is loading from the
                // server
                if (dt) {
                    f.before('chart:pareto:render', function() {
                        this.$("[data-action=loading]").removeClass(this.cssIconDefault).addClass(this.cssIconRefresh)
                    }, {}, dt);
                    f.on('chart:pareto:rendered', function() {
                        this.$("[data-action=loading]").removeClass(this.cssIconRefresh).addClass(this.cssIconDefault)
                    }, dt);
                }
            }, this);
            this.values.on('change:display_manager', this.toggleRepOptionsVisibility, this);
            this.values.on('change:selectedTimePeriod', function(context, timeperiod) {
                this.values.set({timeperiod_id: timeperiod});
            }, this);

            if (this.isOnHomePage === false) {
                this.findModelToListen();
                if (this.dynamicUpdate) {
                    this.listenModel.on('change', this.handleDataChange, this);
                }
                this.listenModel.on('change:assigned_user_id', this.handleAssignedUserChange, this);
            }
        }
    },

    findModelToListen: function() {
        this.listenModel = this.model;
        //BEGIN SUGARCRM flav=ent ONLY
        if (this.forecastConfig.forecast_by == 'RevenueLineItems' && this.context.get('module') == 'Opportunities') {
            // since we are forecasting by RLI but on the Opp Module, we need to find the subpanel for RLI to watch
            // for the changes there
            var ctx = _.find(this.context.children, function(child) {
                return (child.get('module') == 'RevenueLineItems');
            });
            if (ctx && ctx.has('collection')) {
                this.listenModel = ctx.get('collection');
                this.dynamicUpdate = this.checkCollectionForDynamicUpdate();
                // since we have a collection, we need to run the update code once
                this.listenModel.on('reset', this.checkCollectionForDynamicUpdate, this);
            }
        }
        //END SUGARCRM flav=ent ONLY
    },

    //BEGIN SUGARCRM flav=ent ONLY
    checkCollectionForDynamicUpdate: function() {
        return !_.isEmpty(this.listenModel.find(function(model) {
            return (model.get('assigned_user_id') === app.user.get('id'));
        }));
    },
    //END SUGARCRM flav=ent ONLY

    /**
     * How to handle if the assigned_user changes.
     *
     * @param {Object} [model]      The model that changed, if not provided, it will use this.model
     */
    handleAssignedUserChange: function(model) {
        var model = model || this.model,
            canUpdate = model.get("assigned_user_id") === app.user.get('id');

        if (this.dynamicUpdate && canUpdate === false) {
            this.dynamicUpdate = false;
            this.removeRowFromChart(model);
            this.listenModel.off('change', this.handleDataChange, this);
        } else if (this.dynamicUpdate === false && canUpdate) {
            this.dynamicUpdate = true;
            this.addRowToChart(model);
            this.listenModel.on('change', this.handleDataChange, this);
        }
    },

    /**
     * Handler for when the model changes
     * @param {Object} [model]      The model that changed, if not provided, it will use this.model
     */
    handleDataChange: function(model) {
        if (this.values.get('display_manager') === false && this.dynamicUpdate) {
            // we can update this chart
            // get what we are currently filtered by
            // find the item in the serverData
            var model = model || this.model,
                changed = model.changed,
                changedField = _.keys(changed),
                field = this.getField('paretoChart'),
                serverData = field.getServerData();

            // dump out if it's not a field we are watching
            if (_.isEmpty(_.intersection(this.validChangedFields, _.keys(changed)))) {
                return;
            }

            // before we do anything, lets make sure that if the date_changed, make sure it's still in this range,
            // if it's not force the chart to update to the new timeperiod that is valid for this row, then add this
            // row to the new timeperiod
            if (_.contains(changedField, 'date_closed_timestamp')) {
                if (!(model.get('date_closed_timestamp') >= _.first(serverData['x-axis']).start_timestamp &&
                    model.get('date_closed_timestamp') <= _.last(serverData['x-axis']).end_timestamp)) {

                    field.once('chart:pareto:rendered', function() {
                        this.addRowToChart();
                    }, this);
                    this.values.set('timeperiod_id', model.get('date_closed_timestamp'));
                    return;
                }
            }

            // Amount on Opportunity maps to likely in the data set
            if (_.contains(changedField, 'amount')) {
                changed.likely = this._convertCurrencyValue(changed.amount, model.get('base_rate'));
                delete changed.amount;
            }
            // Likely Case in RLI
            if (_.contains(changedField, 'likely_case')) {
                changed.likely = this._convertCurrencyValue(changed.likely_case, model.get('base_rate'));
                delete changed.likely_case;
            }

            if (_.contains(changedField, 'best_case')) {
                changed.best = this._convertCurrencyValue(changed.best_case, model.get('base_rate'));
                delete changed.best_case;
            }
            if (_.contains(changedField, 'worst_case')) {
                changed.worst = this._convertCurrencyValue(changed.worst_case, model.get('base_rate'));
                delete changed.worst_case;
            }

            if (_.contains(changedField, 'commit_stage')) {
                changed.forecast = changed.commit_stage
                delete changed.commit_stage;
            }

            _.find(serverData.data, function(record, i, list) {
                if (model.get('id') == record.record_id) {
                    list[i] = _.extend({}, record, changed);
                    return true;
                }
                return false;
            });

            field.setServerData(serverData, _.contains(changedField, 'probability'));
        }
    },

    /**
     * Add the model to the pareto chart
     * @param {Object} [model]      The Model to add, if not passed in, it will use this.model
     */
    addRowToChart: function(model) {
        var model = model || this.model,
            field = this.getField('paretoChart'),
            serverData = field.getServerData(),
            base_rate = model.get('base_rate'),
            f = {
                best: this._convertCurrencyValue(model.get('best_case'), base_rate),
                likely: this._convertCurrencyValue(model.has('amount') ? model.get('amount') : model.get('likely_case'), base_rate),
                worst: this._convertCurrencyValue(model.get('worst_case'), base_rate),
                record_id: model.get('id'),
                date_closed_timestamp: model.get('date_closed_timestamp'),
                probability: model.get('probability'),
                sales_stage: model.get('sales_stage'),
                forecast: model.get('commit_stage')
            };

        serverData.data.push(f);

        field.setServerData(serverData, true);
    },

    /**
     * Utility Method to convert to base rate
     * @param {Number} value
     * @param {Number} base_rate
     * @returns {Number}
     * @protected
     */
    _convertCurrencyValue: function(value, base_rate) {
        return app.currency.convertWithRate(value, base_rate);
    },

    /**
     * Get the server data from the ParetoField and if the model exists in the data, remove it
     *
     * @param {Object} [model]      The Model to add, if not passed in, it will use this.model
     */
    removeRowFromChart: function(model) {
        var model = model || this.model,
            field = this.getField('paretoChart'),
            serverData = field.getServerData();

        _.find(serverData.data, function(record, i, list) {
            if (model.get('id') == record.record_id) {
                list.splice(i, 1);
                return true;
            }
            return false;
        });

        field.setServerData(serverData, true);
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
        if (this.listenModel) this.listenModel.off(null, null, this);
        if (this.context) this.context.off(null, null, this);
        if (this.values) this.values.off(null, null, this);
        app.view.View.prototype.unbindData.call(this);
    }
})
