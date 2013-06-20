({
    initialize: function(opts) {
        this.server_info = app.metadata.getServerInfo();
        app.view.Layout.prototype.initialize.call(this, opts);
    }
})
