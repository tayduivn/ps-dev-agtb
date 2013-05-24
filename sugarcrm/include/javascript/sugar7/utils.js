(function(app) {
    app.events.on("app:init", function() {
        app.utils = _.extend(app.utils, {
                handleTooltip: function(event, viewComponent) {
                    var $el = viewComponent.$(event.target);
                    if( $el[0].offsetWidth < $el[0].scrollWidth ) {
                        $el.tooltip({placement:"top", container: "body"});
                        $el.tooltip('show');
                    } else {
                        $el.tooltip('destroy');
                    }
                }
        });
    });
})(SUGAR.App);
