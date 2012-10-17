({
    /**
     * Render profile actions dropdown menu
     * @private
     */
    _renderHtml: function() {
        if (!app.api.isAuthenticated() || app.config.appStatus == 'offline') return;

        this.fullName = app.user.get('full_name');
        app.view.View.prototype._renderHtml.call(this);
    }
})