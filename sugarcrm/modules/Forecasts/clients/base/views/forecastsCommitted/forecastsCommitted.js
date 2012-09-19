/**
 * View that displays committed forecasts for current user.  If the manager view is selected, the Forecasts
 * of Rollup type are shown; otherwise the Forecasts of Direct type are shown.
 *
 * @class View.Views.GridView
 * @alias SUGAR.App.layout.GridView
 * @extends View.View
 */
({
    /**
     * The url for the REST endpoint
     */
    url : 'rest/v10/Forecasts/committed',

    /**
     * The class selector representing the element which contains the view output
     */
    viewSelector : '.forecastsCommitted',

    /**
     * Stores the Backbone collection of Forecast models
     */
    _collection : {},

    /*
     * Stores the name to display in the view
     */
    fullName : '',

    /**
     * Stores the best case to display in the view
     */
    bestCase : 0,

    /**
     * Stores the likely case to display in the view
     */
    likelyCase : 0,

    /**
     * Used to query for the user_id value in Forecasts
     */
    userId : '',

    /**
     * Used to query for the timeperiod_id value in Forecasts
     */
    timePeriodId : '',

    /**
     * Used to query for the forecast_type value in Forecasts
     */
    forecastType : 'Direct',

    /**
     * Stores the historical log of the Forecast entries
     */
    historyLog : Array(),

    /**
     * Stores the Forecast totals to use when creating a new entry
     */
    totals : null,

    /**
     * Stores the previous totals to display in the view
     */
    previousTotals : null,

    /**
     * Template to use when updating the bestCase on the committed bar
     */
    bestTemplate : _.template('<%= bestCase %>&nbsp;<span class="icon-sm committed_arrow<%= bestCaseCls %>"></span>'),

    /**
     * Template to use wen updating the likelyCase on the committed bar
     */
    likelyTemplate : _.template('<%= likelyCase %>&nbsp;<span class="icon-sm committed_arrow<%= likelyCaseCls %>"></span>'),

    savedTotal : null,

    runningFetch : false,

    /**
     * Used to determine whether or not to visibly show the Commit log
     */
    showHistoryLog : false,

    /**
     * Used to determine whether or not to visibly show the extended Commit log
     */
    showMoreLog : false,

    /**
     * the timeperiod field metadata that gets used at render time
     */
    timeperiod: {},

    events : {
        'click i[id=show_hide_history_log]' : 'showHideHistoryLog',
        'click div[id=more]' : 'showHideMoreLog'
    },

    initialize : function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this._collection = this.context.forecasts.committed;

        this.fullName = app.user.get('full_name');
        this.userId = app.user.get('id');
        this.forecastType = (app.user.get('isManager') == true && app.user.get('showOpps') == false) ? 'Rollup' : 'Direct';
        this.timePeriodId = app.defaultSelections.timeperiod_id.id;
        this.selectedUser = {id: app.user.get('id'), "isManager":app.user.get('isManager'), "showOpps": false};

        this._collection.url = this.createUrl();

        this.bestCase = 0;
        this.likelyCase = 0;
        this.showHistoryLog = false;

        _.each(this.meta.panels, function(panel) {
            this.timeperiod = _.find(panel.fields, function (item){
                return _.isEqual(item.name, 'timeperiod');
            });
        }, this);
    },

    /**
     * Switch showHistoryLog flag for expanding/collapsing log after commit
     */
    showHideHistoryLog: function() {
        this.showHistoryLog = this.showHistoryLog ? false : true;
        this._render();
    },

    /**
     * Switch showMoreLog flag for expanding/collapsing extended log after commit
     */
    showHideMoreLog: function() {
        this.showMoreLog = this.showMoreLog ? false : true;
        this._render();
    },


    /**
     * Overriding _renderField because we need to set up the events to set the proper value depending on which field is
     * being changed.
     * binary for forecasts and adjusts the category filter accordingly
     * @param field
     * @protected
     */
    _renderField: function(field) {
        if (field.name == "timeperiod") {
            field = this._setUpTimeperiodField(field);
        }
        app.view.View.prototype._renderField.call(this, field);
    },

    /**
     * Sets up the save event and handler for the dropdown fields in the timeperiod view.
     * @param field the commit_stage field
     * @return {*}
     * @private
     */
    _setUpTimeperiodField: function (field) {

        field.events = _.extend({"change select": "_updateSelections"}, field.events);
        field.bindDomChange = function() {};

        /**
         * updates the selection when a change event is triggered from a dropdown
         * @param event the event that was triggered
         * @param input the (de)selection
         * @private
         */
        field._updateSelections = function(event, input) {
            var label = this.$el.find('option:[value='+input.selected+']').text();
            //Set the default selection so that when render is called on the view it will use the newly selected value
            app.defaultSelections.timeperiod_id.id = input.selected;
            this.view.context.forecasts.set('selectedTimePeriod', {"id": input.selected, "label": label});
        };

        // INVESTIGATE: Should this be retrieved from the model, instead of directly?
        app.api.call("read", app.api.buildURL("Forecasts", "timeperiod"), '', {success: function(results) {
            this.field.def.options = results;
            if(!this.field.disposed) {
                this.field.render();
            }
        }}, {field: field, view: this});

        field.def.value = app.defaultSelections.timeperiod_id.id;
        return field;
    },




    /**
     * Renders the component
     */
    _renderHtml : function(ctx, options) {
        app.view.View.prototype._renderHtml.call(this, ctx, options);

        if(this.showHistoryLog) {
            this.$el.find('i[id=show_hide_history_log]').toggleClass('icon-chevron-down icon-chevron-up');
            this.$el.find('div[id=history_log_results]').removeClass('hide');
            if(this.showMoreLog) {
                this.$el.find('div[id=more_log_results]').removeClass('hide');
                this.$el.find('div[id=more]').html('<p><span class=" icon-minus-sign">&nbsp;' + App.lang.get('LBL_LESS', 'Forecasts') + '</span></p><br />');
            }
        }
    },

    createUrl : function() {
        var urlParams = {
            user_id: this.userId,
            timeperiod_id : this.timePeriodId,
            forecast_type : this.forecastType
        };
        return app.api.buildURL('Forecasts', 'committed', '', urlParams);
    },

    updateCommitted: function() {
        this.runningFetch = true;
        this.bestCase = 0;
        this.likelyCase = 0;
        this.likelyCaseCls = '';
        this.bestCaseCls = '';
        this._collection.url = this.createUrl();
        this._collection.fetch();
    },

    bindDataChange: function() {

        var self = this;

        this._collection = this.context.forecasts.committed;
        this._collection.on("reset", function() {
            self.runningFetch = false;
            self.buildForecastsCommitted();
        }, this);
        this._collection.on("change", function() { self.buildForecastsCommitted(); }, this);

        if(this.context && this.context.forecasts) {
            this.context.forecasts.on("change:selectedUser", function(context, user) {
                self.userId = user.id;
                self.fullName = user.full_name;
                self.forecastType = user.showOpps ? 'Direct' : 'Rollup';
                self.selectedUser = user;
                // when ever the users changes, empty out the saved totals
                self.totals = null;
                self.updateCommitted();
            }, this);
            this.context.forecasts.on("change:selectedTimePeriod", function(context, timePeriod) {
                self.timePeriodId = timePeriod.id;
                self.updateCommitted();
            }, this);
            this.context.forecasts.on("change:updatedTotals", function(context, totals) {
                if(self.selectedUser.isManager == true && self.selectedUser.showOpps == false) {
                    return;
                }
                self.updateTotals(totals);
            }, this);
            this.context.forecasts.on("change:updatedManagerTotals", function(context, totals) {
                if(self.selectedUser.isManager == true && self.selectedUser.showOpps == false) {
                    self.updateTotals(totals);
                }
            }, this);
            this.context.forecasts.on("change:commitForecastFlag", function(context, flag) {
                if(flag) {
                    // reset flag without triggering event
                    self.context.forecasts.set({commitForecastFlag : false}, {silent:true})
                    self.commitForecast();
                }
            }, this);
        }
    },

    /**
     * Common code to update the totals
     *
     * @param totals
     */
    updateTotals : function (totals) {
        var self = this;

        var allZero = true;
        _.each(totals, function(value, key) {
            if(key == "timeperiod_id") return;
            if(value != 0) {
                allZero = false;
            }
        });

        if(allZero == true) return;

        // these fields don't matter when it comes to tracking these values so just 0 them out.
        // we don't care about this field
        if(!_.isUndefined(totals.quota)) {
            totals.quota = 0;
        }

        if(!_.isEqual(self.totals, totals)) {

            var best = {};
            var likely = {};
            // get the last committed value
            var previousCommit = (this._collection.models != undefined) ? _.first(this._collection.models) : [];
            if(_.isEmpty(previousCommit) || this.runningFetch == true) {
                self.savedTotal = totals;
                return;
            }

            if(!_.isEmpty(self.savedTotal)) self.savedTotal = null;

            if(self.selectedUser.isManager == true && self.selectedUser.showOpps === false) {
                // management view
                best.bestCaseCls = this.getColorArrow(totals.best_adjusted, previousCommit.get('best_case'));
                best.bestCase = app.currency.formatAmountLocale(totals.best_adjusted);
                likely.likelyCaseCls = this.getColorArrow(totals.likely_adjusted, previousCommit.get('likely_case'));
                likely.likelyCase = app.currency.formatAmountLocale(totals.likely_adjusted);
            } else {
                // sales rep view
                best.bestCaseCls = this.getColorArrow(totals.best_case, previousCommit.get('best_case'));
                best.bestCase = app.currency.formatAmountLocale(totals.best_case);
                likely.likelyCaseCls = this.getColorArrow(totals.amount, previousCommit.get('likely_case'));
                likely.likelyCase = app.currency.formatAmountLocale(totals.amount);
            }

            self.bestCaseCls = best.bestCaseCls;
            self.bestCase = best.bestCase;
            self.likelyCaseCls = likely.likelyCaseCls;
            self.likelyCase = likely.likelyCase;

            $('h2#best').html(this.bestTemplate(best));
            $('h2#likely').html(this.likelyTemplate(likely));

        }

        self.totals = totals;
    },

    /**
     * Utility method to get the arrow and color depending on how the values match up.
     *
     * @param newValue
     * @param currentValue
     * @return {String}
     */
    getColorArrow: function(newValue, currentValue)
    {
        var cls = '';

        cls = (newValue > currentValue) ? ' icon-arrow-up font-green' : ' icon-arrow-down font-red';
        cls = (newValue == currentValue) ? '' : cls;

        return cls
    },

    buildForecastsCommitted: function() {
        var self = this;
        var count = 0;
        var previousModel;

        //Reset the history log
        self.historyLog = [];
        self.moreLog = [];
        self.previousText = "Previous Commit: 0";
        self.previousLikelyCase = 0;
        self.previousBestCase = 0;

        _.each(self._collection.models, function(model)
        {
            //Get the first entry
            if(count == 0)
            {
                previousModel = model;
                var hb = Handlebars.compile(SUGAR.language.get('Forecasts', 'LBL_PREVIOUS_COMMIT'));
                var dateEntered = new Date(Date.parse(previousModel.get('date_entered')));
                if (dateEntered == 'Invalid Date') {
                    dateEntered = previousModel.get('date_entered');
                }
                self.previousText = hb({'likely_case' : App.date.format(dateEntered, app.user.get('datepref') + ' ' + app.user.get('timepref'))});
                self.previousLikelyCase = app.currency.formatAmountLocale(previousModel.get('likely_case'));
                self.previousBestCase = app.currency.formatAmountLocale(previousModel.get('best_case'));
            } else {
                if(count == 1)
                {
                    self.previousText = Handlebars.compile(SUGAR.language.get('Forecasts', 'LBL_PREVIOUS_COMMIT'));
                    self.previousLikelyCase = app.currency.formatAmountLocale(previousModel.get('likely_case'));
                    self.previousBestCase = app.currency.formatAmountLocale(previousModel.get('best_case'));
                    dateEntered = new Date(Date.parse(previousModel.get('date_entered')));
                    self.previousDateEntered = App.date.format(dateEntered, app.user.get('datepref') + ' ' + app.user.get('timepref'));
                }
                self.historyLog.push(app.forecasts.utils.createHistoryLog(model, previousModel));
                previousModel = model;
            }
            count++;
        });

        //Slice everything after the second element and store in historyLog variable
        if(self.historyLog.length > 2)
        {
            self.moreLog = self.historyLog.splice(2, self.historyLog.length);
        }

        self.render();

        if(!_.isEmpty(self.savedTotal)) {
            self.updateTotals(self.savedTotal);
        }
    },

    /**
     * commit the forecast and by creating a forecast entry if the totals have been updated and the new forecast entry
     * is different from the previous one (best_case and likely_case are not exactly identical)
     *
     */
    commitForecast: function() {
        var self = this;

        if(!self.context.forecasts.get('commitButtonEnabled')) {
            return false;
        }

        self.context.forecasts.set({commitButtonEnabled : false});

        //If the totals have not been set, don't save
        if(!self.totals)
        {
            return;
        }

        var forecast = new this._collection.model();
        forecast.url = self.url;
        var user = this.context.forecasts.get('selectedUser');

        var forecastData = {};

        if(user.isManager == true && user.showOpps == false) {
            forecastData.best_case = self.totals.best_adjusted;
            forecastData.likely_case = self.totals.likely_adjusted;
        } else {
            forecastData.best_case = self.totals.best_case;
            forecastData.likely_case = self.totals.amount;
        }
        forecastData.timeperiod_id = self.timePeriodId;
        forecastData.forecast_type = self.forecastType;
        forecastData.amount = self.totals.amount;
        forecastData.opp_count = self.totals.included_opp_count;

        // apply data to model then save
        forecast.set(forecastData);
        forecast.save();

        // clear out the arrows
        self.likelyCaseCls = '';
        self.bestCaseCls = '';

        self.previous = self.totals;
        self._collection.url = self.url;
        self._collection.unshift(forecast);
    }
})
