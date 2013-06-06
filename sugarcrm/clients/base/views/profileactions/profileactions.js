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
        app.view.View.prototype.initialize.call(this, options);

        app.events.on("app:sync:complete", this.setCurrentUserData, this);
        app.user.on("change:picture", this.setCurrentUserData, this);
        app.user.on("change:full_name", this.setCurrentUserData, this);
    },

    /**
     * Render profile actions dropdown menu
     * @private
     */
    _renderHtml: function() {
        if (!app.api.isAuthenticated() || app.config.appStatus == 'offline') return;
        this.showAdmin = app.acl.hasAccess('admin', 'Administration');
        app.view.View.prototype._renderHtml.call(this);
    },
    /**
     * Sets the current user's information like full name, user name, avatar, etc.
     * @protected
     */
    setCurrentUserData: function() {
        this.fullName = app.user.get("full_name");
        this.userName = app.user.get("user_name");

        var picture = app.user.get("picture");
        this.pictureUrl = picture ? app.api.buildFileURL({
            module: "Users",
            id: app.user.get("id"),
            field: "picture"
        }) : '';

        this.render();
    },
    _dispose: function() {
        if (app.user) app.user.off(null, null, this);
        app.view.Component.prototype._dispose.call(this);
    }
})
