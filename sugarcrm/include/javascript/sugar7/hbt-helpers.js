(function(app) {
    app.events.on("app:init", function() {
        Handlebars.registerHelper('modelRoute', function(model, action) {
            action = _.isString(action) ? action : null;
            var id = action == "create" ? "" : model.id,
                url,
                bwcActions = {
                    'create': 'EditView',
                    'edit': 'EditView',
                    'detail': 'DetailView'
                };

            var moduleMeta = app.metadata.getModule(model.module) || {};
            if (moduleMeta.isBwcEnabled) {
                url = app.bwc.buildRoute(model.module, id, bwcActions[action]);
            } else {
                //Normal Sidecar route
                url = app.router.buildRoute(model.module, id, action);
            }
            moduleMeta = null;
            return new Handlebars.SafeString(url);
        });

        Handlebars.registerHelper('moduleIconLabel', function(module) {
            var name = app.lang.getAppListStrings('moduleListSingular')[module] || module;
            return name.substring(0, 2);
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
