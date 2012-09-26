({
    events: {},
    initialize: function(options) {
        var self = this;
        app.view.View.prototype.initialize.call(this, options);
        app.on("app:view:change", function(view, data) {
            self.title = data.title;
            self.render();
        });
    }
})
