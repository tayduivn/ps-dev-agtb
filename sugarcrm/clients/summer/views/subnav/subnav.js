({
    initialize: function(options) {
        var self = this;
        app.view.View.prototype.initialize.call(this, options);
        this.context.on("change", function(view, data) {
            self.title = data.title;
            self.render();
        });
    }
})
