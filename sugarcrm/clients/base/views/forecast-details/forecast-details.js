({
    plugins: ['Dashlet'],

    /**
     * Manager totals for likely_adjusted
     */
    likelyTotal: 0,

    /**
     * Manager totals for best_adjusted
     */
    bestTotal: 0,

    /**
     * Manager totals for worst_adjusted
     */
    worstTotal: 0,

    /**
     * If we need to get the rollup or direct forecast data
     */
    shouldRollup: false,

    /**
     * Necessary for Forecast module as the selectedUser can change and be different from currently-loggged-in user
     */
    selectedUser: {},

    /**
     * Has Forecast module been set up
     */
    isForecastSetup: false,

    /**
     * Is the user a Forecast admin
     */
    isForecastAdmin: false,

    /**
     * Holds the subDetails template so the timeperiod field doesn't re-fetch every re-render
     */
    subDetailsTpl: {},

    /**
     * Holds the dom values for best/likely/worst show/hide dropdown
     */
    detailsDataSet: {},

    /**
     * Config metadata from Forecasts module
     */
    forecastConfig: {},

    /**
     * Holds if the forecasts config has proper closed won/lost keys
     */
    forecastsConfigOK: false,

    /**
     * events on the view for which to watch
     */
    events : {
        'click #forecastsProgressDisplayOptions div.datasetOptions label.radio' : 'changeDisplayOptions'
    },

    /**
     * {@inheritdoc}
     */
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        // check to make sure that forecast is configured
        this.forecastConfig = app.metadata.getModule('Forecasts', 'config');
        this.isForecastSetup = this.forecastConfig.is_setup;
        this.forecastsConfigOK = app.utils.checkForecastConfig();

        if(this.isForecastSetup && this.forecastsConfigOK) {
            // set up the model data
            this.resetModel();

            // since we need the timeperiods from 'Forecasts' set the models module to 'Forecasts'
            this.model.module = 'Forecasts';

            // use the object version of user not a Model
            this.selectedUser = app.user.toJSON();
            this.shouldRollup = this.isManagerView();

            this.isForecastAdmin = _.isUndefined(app.user.getAcls()['Forecasts'].admin);

            // set up the subtemplate
            this.subDetailsTpl = app.template.getView('forecast-details.sub-details');

            this.detailsDataSet = this.setUpShowDetailsDataSet(this.forecastConfig);

            // get the current timeperiod
            app.api.call('GET', app.api.buildURL('TimePeriods/current'), null, {
                success: _.bind(function(o) {
                    // Make sure the model is here when we get back and this isn't mid-pageload or anything
                    if(this.model) {
                        this.model.set({selectedTimePeriod: o.id}, {silent: true});
                        this.loadData();
                    }
                }, this),
                complete: options ? options.complete : null
            });
        }
    },

    /**
     * Returns an object of key: value pairs to be used in the select dropdowns to choose Likely/Best/Worst data to show/hide
     *
     * @param cfg Metadata config object for forecasts
     * @return {Object}
     */
    setUpShowDetailsDataSet: function(cfg) {
        var ds = app.metadata.getStrings('app_list_strings')['forecasts_options_dataset'] || [];

        var returnDs = {};
        _.each(ds, function(value, key) {
            if(cfg['show_worksheet_' + key] == 1) {
                returnDs[key] = value
            }
        }, this);
        return returnDs;
    },

    /**
     * Resets the model to default data
     */
    resetModel: function() {
        this.model.set({
            opportunities : 0,
            revenue : 0,
            closed_amount : 0,
            closed_likely_amount : 0,
            closed_likely_percent : 0,
            closed_likely_distance : 0,
            quota_amount : 0,
            quota_likely_amount : 0,
            quota_likely_percent : 0,
            quota_likely_distance : 0,
            closed_best_amount : 0,
            closed_best_percent : 0,
            closed_best_distance : 0,
            quota_best_amount : 0,
            quota_best_percent : 0,
            quota_best_distance : 0,
            closed_worst_amount : 0,
            closed_worst_percent : 0,
            closed_worst_distance : 0,
            quota_worst_amount : 0,
            quota_worst_percent : 0,
            quota_worst_distance : 0,
            show_details_likely: this.forecastConfig.show_worksheet_likely,
            show_details_best: this.forecastConfig.show_worksheet_best,
            show_details_worst: this.forecastConfig.show_worksheet_worst,
            pipeline : 0,
            quota_amount_str: '',
            closed_amount_str: '',
            revenue_str: '',
            isForecastSetup: this.isForecastSetup,
            isForecastAdmin: this.isForecastAdmin
        });
    },

    /**
     * Builds widget url
     *
     * @return {*} url to call
     */
    getProjectedURL: function() {
        var method = this.shouldRollup ? "progressManager" : "progressRep",
            url = 'Forecasts/' + this.model.get('selectedTimePeriod') + '/' + method + '/' + this.selectedUser.id;

        return app.api.buildURL(url, 'create', null, {module: this.module});
    },

    /**
     * {@inheritdoc}
     */
    bindDataChange: function() {
        var ctx;
        if(this.module == 'Forecasts') {
            ctx = this.context;
        } else {
            ctx = this.model;
        }

        ctx.on('change:selectedTimePeriod', function(model) {
            if(this.module == 'Forecasts') {
                this.updateDetailsForSelectedTimePeriod(model.get('selectedTimePeriod'));
            }
            // reload widget data when the selectedTimePeriod changes
            this.loadData({});
        }, this);
        ctx.on('change:selectedUser', function(model) {
            if(this.module == 'Forecasts') {
                this.updateDetailsForSelectedUser(model.get('selectedUser'));
            }
            // reload widget data when the selectedUser changes
            this.loadData({});
        }, this);

    },

    /**
     * Overrides loadData to load from a custom URL
     *
     * @override
     */
    loadData: function(options) {
        if(!_.isEmpty(this.model.get('selectedTimePeriod'))) {
            var url = this.getProjectedURL(),
                cb = {
                    context: this,
                    success: this.handleNewDataFromServer,
                    complete: options ? options.complete : null
                };

            app.api.call('read', url, null, null, cb);
        }
    },

    /**
     * Used to re-render only the projected data inside the widget so render doesnt
     * get called and dispose the select2 timeperiod field, which would then go
     * re-fetch its data at least once every render
     */
    renderSubDetails: function() {
        if(this.$el && this.subDetailsTpl) {
            this.$el.find('#guages').html(this.subDetailsTpl(this.model.toJSON()));
        }
    },

    /**
     * Success callback function for loadData to call
     *
     * @param data
     */
    handleNewDataFromServer: function(data) {
        var dataObj = {
                quota_amount : data.quota_amount,
                quota_amount_str: app.currency.formatAmountLocale(data.quota_amount)
            },
        // object that holds data for shouldRollup case or !shouldRollup
        // then gets merged back into dataObj after the if/else
            specificCaseModel = {};

        if(this.shouldRollup) {
            // Handle progressManager-specific data
            this.likelyTotal = data.likely_adjusted;
            this.bestTotal = data.best_adjusted;
            this.worstTotal = data.worst_adjusted;

            specificCaseModel = {
                opportunities : data.opportunities,
                closed_amount : data.closed_amount,
                closed_amount_str: app.currency.formatAmountLocale(data.closed_amount),
                revenue : data.pipeline_revenue,
                revenue_str : app.currency.formatAmountLocale(data.pipeline_revenue)
            };

        } else {
            // Handle progressRep-specific data
            this.likelyTotal = data.amount;
            this.bestTotal = data.best_case;
            this.worstTotal = data.worst_case;

            specificCaseModel = {
                closed_amount: data.won_amount,
                closed_amount_str: app.currency.formatAmountLocale(data.won_amount),
                opportunities : 0,
                revenue : 0
            };

            if (app.user.get('id') != this.selectedUser.id) {
                specificCaseModel.revenue = app.math.sub(data.amount, data.includedClosedAmount);
                specificCaseModel.opportunities = app.math.sub(data.included_opp_count, data.includedClosedCount);
            } else {
                specificCaseModel.revenue = app.math.sub(data.overall_amount, app.math.add(data.lost_amount, data.won_amount));
                specificCaseModel.opportunities = app.math.sub(data.total_opp_count, app.math.add(data.lost_count, data.won_count));
            }

            specificCaseModel.revenue_str = app.currency.formatAmountLocale(specificCaseModel.revenue);
        }

        // merge in the model for the specific cases back into dataObj
        _.extend(dataObj, specificCaseModel);

        if(this.model) {
            this.model.set(dataObj);
            this.recalculateModel();
        }
    },

    /**
     * Recalculates most all the values for the template model
     */
    recalculateModel: function () {
        var closedAmt = this.model.get('closed_amount'),
            quotaAmt = this.model.get('quota_amount');

        // We're using the absolute value difference because with the _above vars, if the value was negative
        // we're still using the positive difference but we're changing the label
        this.model.set({
            closed_likely_amount: this.getAbsDifference(this.likelyTotal, closedAmt),
            closed_likely_percent: this.getPercent(this.likelyTotal, closedAmt),
            closed_likely_distance: this.getRowLabel('LIKELY', 'CLOSED', this.likelyTotal, closedAmt),
            closed_best_amount: this.getAbsDifference(this.bestTotal, closedAmt),
            closed_best_percent: this.getPercent(this.bestTotal, closedAmt),
            closed_best_distance: this.getRowLabel('BEST', 'CLOSED', this.bestTotal, closedAmt),
            closed_worst_amount: this.getAbsDifference(this.worstTotal, closedAmt),
            closed_worst_percent: this.getPercent(this.worstTotal, closedAmt),
            closed_worst_distance: this.getRowLabel('WORST', 'CLOSED', this.worstTotal, closedAmt),
            quota_likely_amount: this.getAbsDifference(this.likelyTotal, quotaAmt),
            quota_likely_percent: this.getPercent(this.likelyTotal, quotaAmt),
            quota_likely_distance: this.getRowLabel('LIKELY', 'QUOTA', this.likelyTotal, quotaAmt),
            quota_best_amount: this.getAbsDifference(this.bestTotal, quotaAmt),
            quota_best_percent: this.getPercent(this.bestTotal, quotaAmt),
            quota_best_distance: this.getRowLabel('BEST', 'QUOTA', this.bestTotal, quotaAmt),
            quota_worst_amount: this.getAbsDifference(this.worstTotal, quotaAmt),
            quota_worst_percent: this.getPercent(this.worstTotal, quotaAmt),
            quota_worst_distance: this.getRowLabel('WORST', 'QUOTA', this.worstTotal, quotaAmt),
            pipeline : this.calculatePipelineSize(this.likelyTotal, this.model.get('revenue'))
        });
        this.renderSubDetails();
    },

    /**
     * Determine if one value is bigger than another then build the language key string to be used
     *
     * @param caseStr case string "LIKELY", "BEST", or "WORST"
     * @param stageStr what stage we're looking at: "QUOTA", or "CLOSED"
     * @param caseValue the value of the case
     * @param stageValue the value of the quota or closed amount
     * @return {String} translated language string
     */
    getRowLabel: function (caseStr, stageStr, caseValue, stageValue) {
        var retStr = 'LBL_DISTANCE_';

        if(caseValue > stageValue) {
            retStr += 'ABOVE_' + caseStr + '_FROM_' + stageStr;
        } else {
            retStr += 'LEFT_' + caseStr + '_TO_' + stageStr;
        }

        return app.lang.get(retStr, "Forecasts");
    },

    /**
     * Return the difference of two values and make sure it's a positive value
     *
     * used as a shortcut function for determine best/likely to closed/quota
     * @param caseValue
     * @param stageValue
     * @return {Number}
     */
    getAbsDifference: function (caseValue, stageValue) {
        return app.currency.formatAmountLocale(Math.abs(stageValue - caseValue));
    },

    /**
     * Returns a percent string based on the best/likely/worst case number vs. quota/closed amount
     *
     * @param caseValue likely/best/worst case value
     * @param stageValue the closed/quota amount from the model
     * @return {String}
     */
    getPercent: function (caseValue, stageValue) {
        var percent = 0;
        if(stageValue > 0 && caseValue > 0) {
            // divide the numbers and multiply times 100
            percent = (caseValue / stageValue) * 100;

            if (percent > 1) {
                // round to a whole number
                percent = Math.round(percent);
            } else {
                // Round the less-than-one percent to two decimal places
                // eg. percent=0.1234 -- percent*100 = 12.34, Math.round makes that 12
                // then percent/100 makes that back to 0.12
                percent = Math.round(percent*100)/100;
            }
        }
        return percent + '%';
    },

    /**
     * calculates the pipeline size to one significant figure.
     * @param likelyTotal
     * @param revenue
     * @return {Number}
     */
    calculatePipelineSize: function (likelyTotal, revenue) {
        var ps = 0;
        if (likelyTotal > 0) {
            ps = revenue / likelyTotal;

            // Round to 1 decimal place
            ps = Math.round(ps * 10)/10;
        }

        // This value is used in the template.
        return ps;
    },

    /**
     * checks the selectedUser to make sure it's a manager and if we should show the manager view
     * @return {Boolean}
     */
    isManagerView: function () {
        return this.selectedUser.is_manager == true && (this.selectedUser.showOpps == undefined || this.selectedUser.showOpps === false);
    },

    /**
     * Set the new time period
     *
     * @param {String} timePeriod id in string form
     */
    updateDetailsForSelectedTimePeriod: function (timePeriod) {
        // setting the model will trigger loadData()
        this.model.set({selectedTimePeriod: timePeriod});
    },

    /**
     * Set the new selected user
     *
     * @param {Object} selectedUser
     */
    updateDetailsForSelectedUser: function (selectedUser) {
        // don't directly set model selectedUser so we can handle selectedUser param in case it comes in as
        // just an id or something from somewhere else, so we can set it the right way for this widget
        this.selectedUser.last_name = selectedUser.last_name;
        this.selectedUser.first_name = selectedUser.first_name;
        this.selectedUser.full_name = selectedUser.full_name;
        this.selectedUser.id = selectedUser.id;
        this.selectedUser.isManager = selectedUser.isManager;
        this.selectedUser.reportees = selectedUser.reportees;
        this.selectedUser.showOpps = selectedUser.showOpps;
        this.selectedUser.user_name = selectedUser.user_name;

        this.shouldRollup = this.isManagerView();

        // setting the model will trigger loadData()
        this.model.set({selectedUser: selectedUser});
    },

    /**
     * Event handler to update which dataset is used.
     *
     * @param {jQuery.Event} evt click event
     */
    changeDisplayOptions : function(evt) {
        evt.preventDefault();
        this.handleOptionChange(evt);
    },

    /**
     * Handle the click event for the optins menu
     *
     * @param {jQuery.Event} evt click event
     */
    handleOptionChange: function(evt) {
        var $el = $(evt.currentTarget),
            changedSegment = $el.attr('data-set');

        //check what needs to be done to the target
        if($el.hasClass('checked')) {
            //item was checked, uncheck it
            $el.removeClass('checked');
            $('div .projected_' + changedSegment).hide();
        } else {
            //item was unchecked and needs checked now
            $el.addClass('checked');
            $('div .projected_' + changedSegment).show();
        }
    }
})
