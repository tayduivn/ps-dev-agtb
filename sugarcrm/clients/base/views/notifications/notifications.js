({
    plugins: ['dropdown'],
    events: {
        'click .dropdown-toggle':'toggleDropdown'
    },
    toggleDropdown: function(event) {
        var $currentTarget = this.$(event.currentTarget);
        this.toggleDropdownHTML($currentTarget);
    },
    initialize: function(options) {
        app.events.on("app:sync:complete", this.render, this);
        app.view.View.prototype.initialize.call(this, options);
    },

    _renderHtml: function() {
        if (!app.api.isAuthenticated() || app.config.appStatus == 'offline') return;

        app.view.View.prototype._renderHtml.call(this);
    }
})