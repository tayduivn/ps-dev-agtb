(function (app) {
    app.events.on("app:init", function () {
        app.plugins.register('timeago', ['view'], {
            onAttach: function(component, plugin) {
                component.on('render', function() {
                    if ($.fn.timeago) {
                        component.$("span.relativetime").timeago({
                            logger: SUGAR.App.logger,
                            date: SUGAR.App.date,
                            lang: SUGAR.App.lang,
                            template: SUGAR.App.template
                        });
                    }
                });
            }
        });
    });
})(SUGAR.App);
