//FILE SUGARCRM flav=ent ONLY
({
    extendsFrom: 'PreviewView',

    /**
     * Gets the portal status from metadata to know if we render portal specific fields.
     * @override
     * @param options
     */
    _previewifyMetadata: function(meta) {
        meta = app.view.invokeParent(this, {type: 'view', name: 'preview', method: '_previewifyMetadata', args: [meta]});
        var fakeView = {};
        fakeView.meta = meta;
        app.view.invokeParent(fakeView, {
            type: 'view',
            name: 'record',
            module: 'Contacts',
            method: 'removePortalFieldsIfPortalNotActive',
            args: []
        });
        return fakeView.meta;
    }
})
