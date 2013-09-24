(function (app) {
    app.events.on("app:init", function () {
        app.plugins.register('Timeago', ['view'], {
            onAttach: function(component, plugin) {
                component.on('render', function() {
                    if ($.fn.timeago) {
                        component.$("span.relativetime").timeago({
                            logger: SUGAR.App.logger,
                            date: SUGAR.App.date,
                            lang: SUGAR.App.lang,
                            template: SUGAR.App.template,
                            dateFormat: app.user.getPreference('datepref'),
                            timeFormat: app.user.getPreference('timepref')
                        });
                    }
                });
            }
        });
    });
})(SUGAR.App);
