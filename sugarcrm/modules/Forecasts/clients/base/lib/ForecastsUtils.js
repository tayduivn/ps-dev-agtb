(function(app) {
    if(!_.has(app, 'forecasts')) {
        app.forecasts = {}
    }
    app.forecasts.utils = {

        /**
         * Takes two Forecasts models and returns HTML for the history log
         *
         * @param oldestModel {BackboneModel} the oldest model by date_entered
         * @param newestModel {BackboneModel} the most recent model by date_entered
         * @return {Object}
         */
        createHistoryLog: function(oldestModel, newestModel) {
            if(_.isEmpty(oldestModel)) {
                oldestModel = new Backbone.Model({
                    best_case : 0,
                    likely_case: 0,
                    date_entered: ''

                })
            }
            var best_difference = newestModel.get('best_case') - oldestModel.get('best_case');
            var best_changed = best_difference != 0;
            var best_direction = best_difference > 0 ? 'LBL_UP' : (best_difference < 0 ? 'LBL_DOWN' : '');
            var likely_difference = newestModel.get('likely_case') - oldestModel.get('likely_case');
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
                args[1] = app.currency.formatAmountLocale(Math.abs(best_difference));
                args[2] = app.currency.formatAmountLocale(newestModel.get('best_case'));
                args[3] = App.lang.get(likely_direction, 'Forecasts') + likely_arrow;
                args[4] = app.currency.formatAmountLocale(Math.abs(likely_difference));
                args[5] = app.currency.formatAmountLocale(newestModel.get('likely_case'));
                text = 'LBL_COMMITTED_HISTORY_BOTH_CHANGED';
            } else if (!best_changed && likely_changed) {
                args[0] = App.lang.get(likely_direction, 'Forecasts') + likely_arrow;
                args[1] = app.currency.formatAmountLocale(Math.abs(likely_difference));
                args[2] = app.currency.formatAmountLocale(newestModel.get('likely_case'));
                text = 'LBL_COMMITTED_HISTORY_LIKELY_CHANGED';
            } else if (best_changed && !likely_changed) {
                args[0] = App.lang.get(best_direction, 'Forecasts') + best_arrow;
                args[1] = app.currency.formatAmountLocale(Math.abs(best_difference));
                args[2] = app.currency.formatAmountLocale(newestModel.get('best_case'));
                text = 'LBL_COMMITTED_HISTORY_BEST_CHANGED';
            }

            //Compile the language string for the log
            var hb = Handlebars.compile("{{str_format key module args}}");
            var text = hb({'key' : text, 'module' : 'Forecasts', 'args' : args});

            // Check for first time run -- no date_entered for oldestModel
            var oldestDateEntered = oldestModel.get('date_entered');

            // This will always have a value
            var newestModelDate = new Date(Date.parse(newestModel.get('date_entered')));
            var text2 = '';

            if(!_.isEmpty(oldestDateEntered)) {
                var oldestModelDate = new Date(Date.parse(oldestDateEntered));

                var yearDiff = oldestModelDate.getYear() - newestModelDate.getYear();
                var monthsDiff = oldestModelDate.getMonth() - newestModelDate.getMonth();

                if(yearDiff == 0 && monthsDiff < 2)
                {
                    args = [newestModelDate.toString()];
                    text2 = hb({'key' : 'LBL_COMMITTED_THIS_MONTH', 'module' : 'Forecasts', 'args' : args});
                } else {
                    args = [monthsDiff, newestModelDate.toString()];
                    text2 = hb({'key' : 'LBL_COMMITTED_MONTHS_AGO', 'module' : 'Forecasts', 'args' : args});
                }
            } else {
                args = [newestModelDate.toString()];
                text2 = hb({'key' : 'LBL_COMMITTED_THIS_MONTH', 'module' : 'Forecasts', 'args' : args});
            }

            // need to tell Handelbars not to escape the string when it renders it, since there might be
            // html in the string
            return {'text' : new Handlebars.SafeString(text), 'text2' : new Handlebars.SafeString(text2)};
        },

        /**
         * Takes a date from the database in yyyy-mm-dd hh:mm:ss and returns a Date()
         *
         * @param dateStr
         * @return {Date} or null if we didnt receive a properly formatted date
         */
        parseDBDate: function(dateStr) {
            var retDate = null;
            if(!_.isUndefined(dateStr)) {
                var dateTime = dateStr.split(' ');
                // if there is only one element, we didn't receive a properly formatted date
                if(dateTime.length > 1) {
                    var dateArr = dateTime[0].split('-');
                    var timeArr = dateTime[1].split(':');

                    retDate =  new Date(
                        dateArr[0],
                        dateArr[1],
                        dateArr[2],
                        timeArr[0],
                        timeArr[1],
                        timeArr[2]
                    );
                }
            }
            return retDate;
        },

        /**
         * Contains a list of column names from metadata and maps them to correct config param
         * e.g. 'likely_case' column is controlled by the context.forecasts.config.get('show_worksheet_likely') param
         * Used by forecastsWorksheetManager, forecastsWorksheetManagerTotals
         *
         * @property tableColumnsConfigKeyMapManager
         * @private
         */
        _tableColumnsConfigKeyMapManager: {
            'likely_case': 'show_worksheet_likely',
            'likely_adjusted': 'show_worksheet_likely',
            'best_case': 'show_worksheet_best',
            'best_adjusted': 'show_worksheet_best',
            'worst_case': 'show_worksheet_worst',
            'worst_adjusted': 'show_worksheet_worst'
        },

        /**
         * Contains a list of column names from metadata and maps them to correct config param
         * e.g. 'likely_case' column is controlled by the context.forecasts.config.get('show_worksheet_likely') param
         * Used by forecastsWorksheet, forecastsWorksheetTotals, forecastSchedule
         *
         * @property tableColumnsConfigKeyMapRep
         * @private
         */
        _tableColumnsConfigKeyMapRep: {
            'likely_case': 'show_worksheet_likely',
            'best_case': 'show_worksheet_best',
            'worst_case': 'show_worksheet_worst',

            // used for forecastSchedule
            'expected_amount': 'show_worksheet_likely',
            'expected_best_case': 'show_worksheet_best',
            'expected_worst_case': 'show_worksheet_worst'
        },

        /**
         * Function checks the proper _tableColumnsConfigKeyMap___ for the key and returns the config setting
         *
         * @param key {String} table key name (eg: 'likely_case', 'expected_amount')
         * @param viewName {String} the name of the view calling the function (eg: 'forecastSchedule', 'forecastsWorksheet')
         * @param configCtx {Backbone.Model} the config context model from the view
         * @return {*}
         */
        getColumnVisFromKeyMap : function(key, viewName, configCtx) {
            var moduleMap = {
                'forecastSchedule' : 'rep',
                'forecastsWorksheet' : 'rep',
                'forecastsWorksheetTotals' : 'rep',
                'forecastsWorksheetManager' : 'mgr',
                'forecastsWorksheetManagerTotals' : 'mgr'
            }

            // which key map to use from the moduleMap
            var whichKeyMap = moduleMap[viewName];

            // get the proper keymap
            var keyMap = (whichKeyMap === 'rep') ? this._tableColumnsConfigKeyMapRep : this._tableColumnsConfigKeyMapManager;

            var returnValue = configCtx.get(keyMap[key]);
            // If we've been passed a value that doesnt exist in the keymaps
            if(!_.isUndefined(returnValue)) {
                // convert it to boolean
                returnValue = returnValue == 1
            } else {
                // if return value was null (not found) then set to true
                returnValue = true;
            }
            return returnValue;
        }
    };
})(SUGAR.App);