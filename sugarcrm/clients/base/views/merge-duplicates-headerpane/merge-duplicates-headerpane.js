({
    extendsFrom: 'HeaderpaneView',

    events: {
        'click a[name=cancel_button]': 'cancel',
        'click a[name=save_button]': 'save'
    },

    /**
     *
     * {@inheritdoc}
     */
    initialize: function(options) {
        app.view.invokeParent(this, {
            type: 'view',
            name: 'headerpane',
            method: 'initialize',
            args: [options]
        });
        var records = options.context.get("selectedDuplicates");
        this.title = app.lang.get('LBL_MERGING_RECORDS', this.module, {mergeCount: records.length});
    },
    
    /**
     * Cancel and close the drawer
     */
    cancel: function() {
        app.drawer.close();
    },
    
    /**
     * Save primary and delete other records
     */
    save: function() {
        this.layout.trigger("mergeduplicates:save:fire");
    }
})
