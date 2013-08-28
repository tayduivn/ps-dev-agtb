({
    /**
     * {@inheritdocs}
     */
    initialize: function(options) {
        var acls = app.user.getAcls().Forecasts,
            hasAccess = (!_.has(acls, 'access') || acls.access == 'yes'),
            isSysAdmin = (app.user.get('type') == 'admin'),
            isDev = (!_.has(acls, 'developer') || acls.developer == 'yes');
        // if user has access AND is a System Admin OR has a Developer role
        if(hasAccess && (isSysAdmin || isDev)) {
            // initialize
            app.view.Layout.prototype.initialize.call(this, options);
            // load the data
            app.view.Layout.prototype.loadData.call(this);
        } else {
            this.codeBlockForecasts('LBL_FORECASTS_NO_ACCESS_TO_CFG_TITLE', 'LBL_FORECASTS_NO_ACCESS_TO_CFG_MSG');
        }
    },

    /**
     * Blocks forecasts from continuing to load
     */
    codeBlockForecasts: function(title, msg) {
        var alert = app.alert.show('no_access_to_forecasts', {
            level: 'error',
            autoClose: false,
            title: app.lang.get(title, "Forecasts") + ":",
            messages: [app.lang.get(msg, "Forecasts")]
        });

        alert.getCloseSelector().on('click', function() {
            alert.getCloseSelector().off();
            app.router.navigate('#Home', {trigger: true});
        });
    },

    /**
     * Overrides loadData to defer it running until we call it in _onceInitSelectedUser
     *
     * @override
     */
    loadData: function() {
    }
})
