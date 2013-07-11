(function(app) {
    app.events.on("app:init", function() {

        /**
         * Handlebar helper to get the letters used for the icons shown in various headers for each module, based on the
         * translated singular module name.  This does not always match the name of the module in the model,
         * i. e. Product == Revenue Line Item
         * If the module has an icon string defined, use it, otherwise fall back to the module's translated name.
         * If there are spaces in the name, (e. g. Revenue Line Items or Product Catalog), it takes the initials
         * from the first two words, instead of the first two letters (e. g. RL and PC, instead of Re and Pr)
         * @param {String} module to which the icon belongs
         */
        Handlebars.registerHelper('moduleIconLabel', function(module) {
            var name = app.lang.getAppListStrings('moduleIconList')[module] ||
                    app.lang.getAppListStrings('moduleListSingular')[module] ||
                    module,
                space = name.indexOf(" ");

            return (space != -1) ? name.substring(0 , 1) + name.substring(space + 1, space + 2) : name.substring(0, 2);
        });

        /**
         * Handlebar helper to get the Tooltip used for the icons shown in various headers for each module, based on the
         * translated singular module name.  This does not always match the name of the module in the model,
         * i. e. Product == Revenue Line Item
         * @param {String} module to which the icon belongs
         */
        Handlebars.registerHelper('moduleIconToolTip', function(module) {
            return app.lang.getAppListStrings('moduleListSingular')[module] || module;
        });

        /**
         * display timeago for twitter
         */
        Handlebars.registerHelper('twitter_timeago', function(date) {
            var rightNow = new Date(),
                localDate = new Date(date),
                diff = rightNow - localDate,
                second = 1000,
                minute = second * 60,
                hour = minute * 60,
                day = hour * 24,
                strDate = '';

            if ( isNaN(diff) || diff < 0 ) {
                return strDate;
            } else if (diff < day) {
                if (diff < minute) {
                    strDate = Math.floor(diff / second) + ' ' +  app.lang.getAppString('LBL_TWITTER_TIME_AGO_SECONDS');
                } else if (diff < hour) {
                    strDate = Math.floor(diff / minute) + ' ' + app.lang.getAppString('LBL_TWITTER_TIME_AGO_MINUTES');
                } else {
                    strDate = Math.floor(diff / hour) + ' ' + app.lang.getAppString('LBL_TWITTER_TIME_AGO_HOURS');
                }
            } else {
                strDate = SUGAR.App.date.format(localDate, 'j') + ' ' + app.lang.getAppListStrings('dom_cal_month_long')[SUGAR.App.date.format(localDate, 'n')];
            }
            return strDate;
        });
    });
})(SUGAR.App);
