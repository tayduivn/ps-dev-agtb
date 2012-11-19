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
            var is_first_commit = false;

            if(_.isEmpty(oldestModel)) {
                oldestModel = new Backbone.Model({
                    best_case : 0,
                    likely_case: 0,
                    worst_case: 0,
                    date_entered: ''
                })
                is_first_commit = true;
            }
            var best_difference = newestModel.get('best_case') - oldestModel.get('best_case'),
                best_changed = best_difference != 0,
                best_direction = best_difference > 0 ? 'LBL_UP' : (best_difference < 0 ? 'LBL_DOWN' : ''),
                likely_difference = newestModel.get('likely_case') - oldestModel.get('likely_case'),
                likely_changed = likely_difference != 0,
                likely_direction = likely_difference > 0 ? 'LBL_UP' : (likely_difference < 0 ? 'LBL_DOWN' : ''),
                worst_difference = newestModel.get('worst_case') - oldestModel.get('worst_case'),
                worst_changed = worst_difference != 0,
                worst_direction = worst_difference > 0 ? 'LBL_UP' : (worst_difference < 0 ? 'LBL_DOWN' : ''),
                args = [],
                text = 'LBL_COMMITTED_HISTORY_NONE_CHANGED',
                best_arrow = this.getArrowDirectionSpan(best_direction),
                likely_arrow = this.getArrowDirectionSpan(likely_direction),
                worst_arrow = this.getArrowDirectionSpan(worst_direction);

            //determine what changed and add parts to the array for displaying the changes
            if(best_changed) {
                args.push(App.lang.get(best_direction, 'Forecasts') + best_arrow);
                args.push(app.currency.formatAmountLocale(Math.abs(best_difference)));
                args.push(app.currency.formatAmountLocale(newestModel.get('best_case')));
            }

            if(likely_changed) {
                args.push(App.lang.get(likely_direction, 'Forecasts') + likely_arrow);
                args.push(app.currency.formatAmountLocale(Math.abs(likely_difference)));
                args.push(app.currency.formatAmountLocale(newestModel.get('likely_case')));
            }

            if(worst_changed) {
                args.push(App.lang.get(worst_direction, 'Forecasts') + worst_arrow);
                args.push(app.currency.formatAmountLocale(Math.abs(worst_difference)));
                args.push(app.currency.formatAmountLocale(newestModel.get('worst_case')));
            }

            //get label that will be used for the history changes
            text = this.getCommittedHistoryLabel(best_changed, likely_changed, worst_changed, is_first_commit);

            //Compile the language string for the log
            var hb = Handlebars.compile("{{str_format key module args}}"),
                text = hb({'key' : text, 'module' : 'Forecasts', 'args' : args});

            // Check for first time run -- no date_entered for oldestModel
            var oldestDateEntered = oldestModel.get('date_entered');

            // This will always have a value and Format the date according to the user date and time preferences
            var newestModelDate = new Date(Date.parse(newestModel.get('date_entered'))),
                text2 = '',
                newestModelDisplayDate = app.date.format(newestModelDate, app.user.get('datepref') + ' ' + app.user.get('timepref'));

            if(!_.isEmpty(oldestDateEntered)) {
                var oldestModelDate = new Date(Date.parse(oldestDateEntered)),
                    yearDiff = oldestModelDate.getYear() - newestModelDate.getYear(),
                    monthsDiff = oldestModelDate.getMonth() - newestModelDate.getMonth();

                if(yearDiff == 0 && monthsDiff < 2)
                {
                    args = [newestModelDisplayDate];
                    text2 = hb({'key' : 'LBL_COMMITTED_THIS_MONTH', 'module' : 'Forecasts', 'args' : args});
                } else {
                    args = [monthsDiff, newestModelDisplayDate];
                    text2 = hb({'key' : 'LBL_COMMITTED_MONTHS_AGO', 'module' : 'Forecasts', 'args' : args});
                }
            } else {
                args = [newestModelDisplayDate];
                text2 = hb({'key' : 'LBL_COMMITTED_THIS_MONTH', 'module' : 'Forecasts', 'args' : args});
            }

            // need to tell Handelbars not to escape the string when it renders it, since there might be
            // html in the string
            return {'text' : new Handlebars.SafeString(text), 'text2' : new Handlebars.SafeString(text2)};
        },

        /**
         * checks the direction class passed in to determine what span to create to show the appropriate arrow
         * or lack of arrow to display on the
         * @param directionClass class being used for the label ('LBL_UP' or 'LBL_DOWN')
         * @return {String}
         */
        getArrowDirectionSpan: function (directionClass) {
            return directionClass == "LBL_UP" ? '&nbsp;<span class="icon-arrow-up font-green"></span>' :
                directionClass == "LBL_DOWN" ? '&nbsp;<span class="icon-arrow-down font-red"></span>' : '';
         },

        /**
         * builds the string to look up for the history label based on what has changed in the model
         * @param best_changed {bool}
         * @param likely_changed {bool}
         * @param worst_changed {bool}
         * @param is_first_commit {bool}
         * @return {String}
         */
        getCommittedHistoryLabel: function(best_changed, likely_changed, worst_changed, is_first_commit) {
            var labelText = "LBL_COMMITTED_HISTORY";

            labelText += best_changed ? "_BEST" : "";
            labelText += likely_changed ? "_LIKELY" : "";
            labelText += worst_changed ? "_WORST" : "";

            return labelText + (!is_first_commit ? "_CHANGED" : "_SETUP");
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
         * Used by forecastsWorksheet, forecastsWorksheetTotals
         *
         * @property tableColumnsConfigKeyMapRep
         * @private
         */
        _tableColumnsConfigKeyMapRep: {
            'likely_case': 'show_worksheet_likely',
            'best_case': 'show_worksheet_best',
            'worst_case': 'show_worksheet_worst'
        },

        /**
         * Function checks the proper _tableColumnsConfigKeyMap___ for the key and returns the config setting
         *
         * @param key {String} table key name (eg: 'likely_case')
         * @param viewName {String} the name of the view calling the function (eg: 'forecastsWorksheet')
         * @param configCtx {Backbone.Model} the config context model from the view
         * @return {*}
         */
        getColumnVisFromKeyMap : function(key, viewName, configCtx) {
            var moduleMap = {
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