({
    initialize: function(options) {
        app.events.on("app:sync:complete", this.render, this);
        app.events.on("app:login:success", this.render, this);
        app.events.on("app:logout", this.render, this);
        app.view.View.prototype.initialize.call(this, options);
        app.api.call('GET', app.api.buildURL('google/recommend'), null, {
            success: function(o) {
                this.data = o.invites;
            }
        });
    }
})
