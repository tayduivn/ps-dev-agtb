({
    /**
     * Render profile actions dropdown menu
     * @private
     */
    _renderHtml: function() {
        if (!this.app.api.isAuthenticated() || this.app.config.appStatus == 'offline') return;

        this.fullName = this.app.user.get('full_name');
        this.app.view.View.prototype._renderHtml.call(this);
    }
})