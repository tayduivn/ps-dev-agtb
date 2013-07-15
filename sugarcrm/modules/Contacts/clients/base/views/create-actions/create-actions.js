//FILE SUGARCRM flav=ent ONLY
({
    extendsFrom: 'CreateView',

    /**
     * Gets the portal status from metadata to know if we render portal specific fields.
     * @override
     * @param options
     */
    initialize: function(options) {
        app.view.invokeParent(this, {type: 'view', name: 'create', method: 'initialize', args: [options]});
        app.view.invokeParent(this, {
            type: 'view',
            name: 'record',
            module: 'Contacts',
            method: 'removePortalFieldsIfPortalNotActive',
            args: []
        });
    }
})
