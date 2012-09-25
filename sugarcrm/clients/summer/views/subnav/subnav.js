({
    events: {},
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        if('ActivityStream' == this.module) {
            this.title = "My Dashboard";
        }
    }
})
