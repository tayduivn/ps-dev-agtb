({
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

    setCurrentUserData: function() {
        this.fullName = app.user.get("full_name");
        this.extAccts = app.acl.hasAccess("read", "EAPM") ? app.lang.getAppListStrings("moduleList")["EAPM"] : "";
        this.userName = app.user.get("user_name");

        var picture = app.user.get("picture");
        this.pictureUrl = (picture) ? app.api.buildFileURL({
            module: "Users",
            id: app.user.get("id"),
            field: "picture"
        }) : app.config.siteUrl + "/styleguide/assets/img/profile.png";

        this.render();
    },
    _dispose: function() {
        if (app.user) app.user.off(null, null, this);
        app.view.Component.prototype._dispose.call(this);
    }
})
