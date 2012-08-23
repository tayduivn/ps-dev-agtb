(function(app) {
    if(!_.has(app, 'forecasts')) {
        app.forecasts = {}
    }
    app.forecasts.utils = {

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

        }
    };
})(SUGAR.App);