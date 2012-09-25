({
    events: {},
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.title = this.context.get('title');
    }
})
