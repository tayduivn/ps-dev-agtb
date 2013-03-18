({
    initialize: function(opts) {
        if (!opts.meta) return;

        _.each(opts.meta.components, function(component) {
            component.context = component.context || {};
            component.context.hideAlertsOn = "read";
        });
        app.view.Layout.prototype.initialize.call(this, opts);
    }
})
