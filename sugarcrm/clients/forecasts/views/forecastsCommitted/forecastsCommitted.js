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
     * Renders the component
     * @private
     */
    _render: function() {
        app.view.View.prototype._render.call(this);

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
        // we don't care about this field
        if(!_.isUndefined(totals.amount)) {
            totals.amount = 0;
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
                best.bestCase = App.utils.formatNumber(totals.best_adjusted, 0, 0, ',', '.');
                likely.likelyCaseCls = this.getColorArrow(totals.likely_adjusted, previousCommit.get('likely_case'));
                likely.likelyCase = App.utils.formatNumber(totals.likely_adjusted, 0, 0, ',', '.');
            } else {
                // sales rep view
                best.bestCaseCls = this.getColorArrow(totals.best_case, previousCommit.get('best_case'));
                best.bestCase = App.utils.formatNumber(totals.best_case, 0, 0, ',', '.');
                likely.likelyCaseCls = this.getColorArrow(totals.likely_case, previousCommit.get('likely_case'));
                likely.likelyCase = App.utils.formatNumber(totals.likely_case, 0, 0, ',', '.');
            }

            self.bestCaseCls = best.bestCaseCls;
            self.bestCase = best.bestCase;
            self.likelyCaseCls = likely.likelyCaseCls;
            self.likelyCase = likely.likelyCase;

            $('h2#best').html(this.bestTemplate(best));
            $('h2#likely').html(this.likelyTemplate(likely));

            var commitButtonEnabled = self.context.forecasts.get('commitButtonEnabled');
            if((!_.isEmpty(self.bestCaseCls) || !_.isEmpty(self.likelyCaseCls)) && commitButtonEnabled == false) {
                // it's different so we should enable the commit button
                self.context.forecasts.set({commitButtonEnabled : true});
            } else if(_.isEmpty(self.bestCaseCls) && _.isEmpty(self.likelyCaseCls) && commitButtonEnabled == true) {
                self.context.forecasts.set({commitButtonEnabled : false});
            }
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
                self.previousText = hb({'likely_case' : previousModel.get('date_entered')});
                self.previousLikelyCase = App.utils.formatNumber(previousModel.get('likely_case'), 0, 0, ',', '.');
                self.previousBestCase = App.utils.formatNumber(previousModel.get('best_case'), 0, 0, ',', '.');
            } else {
              if(count == 1)
              {
                  self.previousText = Handlebars.compile(SUGAR.language.get('Forecasts', 'LBL_PREVIOUS_COMMIT'));
                  self.previousLikelyCase = App.utils.formatNumber(previousModel.get('likely_case'), 0, 0, ',', '.');
                  self.previousBestCase = App.utils.formatNumber(previousModel.get('best_case'), 0, 0, ',', '.');
                  var dateEntered = App.date.parse(model.get('date_entered'));
                  // TODO: user preferences are not working for formatting dates, hard code for now
                  self.previousDateEntered = App.date.format(dateEntered, 'Y-m-d \\at g:i a');
              }
              self.historyLog.push(self.createHistoryLog(model, previousModel));
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

    createHistoryLog: function(current, previousModel) {
        var best_difference = previousModel.get('best_case') - current.get('best_case');
        var best_changed = best_difference != 0;
        var best_direction = best_difference > 0 ? 'LBL_UP' : (best_difference < 0 ? 'LBL_DOWN' : '');
        var likely_difference = previousModel.get('likely_case') - current.get('likely_case');
        var likely_changed = likely_difference != 0;
        var likely_direction = likely_difference > 0 ? 'LBL_UP' : (likely_difference < 0 ? 'LBL_DOWN' : '');
        var args = Array();
        var text = 'LBL_COMMITTED_HISTORY_NONE_CHANGED';

        var best_arrow = '';
        if(best_direction == "LBL_UP") {
            best_arrow = '&nbsp;<span class="icon-arrow-up font-green"></span>'
        } else if(best_direction == "LBL_DOWN") {
            best_arrow = '&nbsp;<span class="icon-arrow-down font-red"></span>'
        }

        var likely_arrow = '';
        if(likely_direction == "LBL_UP") {
            likely_arrow = '&nbsp;<span class="icon-arrow-up font-green"></span>'
        } else if(likely_direction == "LBL_DOWN") {
            likely_arrow = '&nbsp;<span class="icon-arrow-down font-red"></span>'
        }

        if(best_changed && likely_changed)
        {
            args[0] = App.lang.get(best_direction, 'Forecasts') + best_arrow;
            args[1] = "$" + App.utils.formatNumber(Math.abs(best_difference), 0, 0, ',', '.');
            args[2] = "$" + App.utils.formatNumber(previousModel.get('best_case'), 0, 0, ',', '.');
            args[3] = App.lang.get(likely_direction, 'Forecasts') + likely_arrow;
            args[4] = "$" + App.utils.formatNumber(Math.abs(likely_difference), 0, 0, ',', '.');
            args[5] = "$" + App.utils.formatNumber(previousModel.get('likely_case'), 0, 0, ',', '.');
            text = 'LBL_COMMITTED_HISTORY_BOTH_CHANGED';
        } else if (!best_changed && likely_changed) {
            args[0] = App.lang.get(likely_direction, 'Forecasts') + likely_arrow;
            args[1] = "$" + App.utils.formatNumber(Math.abs(likely_difference), 0, 0, ',', '.');
            args[2] = "$" + App.utils.formatNumber(current.get('likely_case'), 0, 0, ',', '.');
            text = 'LBL_COMMITTED_HISTORY_LIKELY_CHANGED';
        } else if (best_changed && !likely_changed) {
            args[0] = App.lang.get(best_direction, 'Forecasts') + best_arrow;
            args[1] = "$" + App.utils.formatNumber(Math.abs(best_difference), 0, 0, ',', '.');
            args[2] = "$" + App.utils.formatNumber(current.get('best_case'), 0, 0, ',', '.');
            text = 'LBL_COMMITTED_HISTORY_BEST_CHANGED';
        }

        //Compile the language string for the log
        var hb = Handlebars.compile("{{str_format key module args}}");
        var text = hb({'key' : text, 'module' : 'Forecasts', 'args' : args});

        var current_date = App.date.parse(current.get('date_entered'));
        var previous_date = App.date.parse(previousModel.get('date_entered'));

        var yearDiff = current_date.getYear() - previous_date.getYear();
        var monthsDiff = current_date.getMonth() - previous_date.getMonth();

        var text2 = '';

        if(yearDiff == 0 && monthsDiff < 2)
        {
          hb = Handlebars.compile("{{str_format key module args}}");
          args = [previous_date.toString()];
          text2 = hb({'key' : 'LBL_COMMITTED_THIS_MONTH', 'module' : 'Forecasts', 'args' : args});
        } else {
          hb = Handlebars.compile("{{str_format key module args}}");
          args = [monthsDiff, previous_date.toString()];
          text2 = hb({'key' : 'LBL_COMMITTED_MONTHS_AGO', 'module' : 'Forecasts', 'args' : args});
        }

        // need to tell Handelbars not to escape the string when it renders it, since there might be
        // html in the string
        return {'text' : new Handlebars.SafeString(text), 'text2' : new Handlebars.SafeString(text2)};

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
            forecastData.likely_case = self.totals.likely_case;
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
