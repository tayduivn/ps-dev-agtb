({
    extendsFrom: 'HeaderpaneView',

    events: {
        'click a[name=cancel_button]': 'cancel',
        'click a[name=save_button]': 'save',
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
