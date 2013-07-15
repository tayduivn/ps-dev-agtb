//FILE SUGARCRM flav=ent ONLY
({
    /**
     * Gets the portal status from metadata to know if we render portal specific fields.
     * @override
     * @param options
     */
    initialize: function(options) {
        app.view.invokeParent(this, {type: 'view', name: 'record', method: 'initialize', args: [options]});
        this.removePortalFieldsIfPortalNotActive();
    },
    /**
     * Check if portal is active. If not, will remove the portal fields from the metadata
     * THIS METHOD IS CALLED BY create-action.js AND preview.js
     */
    removePortalFieldsIfPortalNotActive: function() {
        //Portal specific fields to hide if portal is disabled
        var portalFields = ['portal_name', 'portal_active', 'portal_password'];

        var serverInfo = app.metadata.getServerInfo();
        if (!serverInfo.portal_active) {
            _.each(this.meta && this.meta.panels, function(panel) {
                //Remove portal fields from panel
                panel.fields = _.reject(panel.fields, function(field) {
                    if (_.isString(field)) {
                        return _.indexOf(portalFields, field) > -1;
                    } else {
                        return _.indexOf(portalFields, field.name) > -1;
                    }
                });
            });
        }
    }
})
