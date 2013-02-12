({
    extendsFrom: 'HeaderpaneView',

    events: {
        'click a[name=cancel_button]': 'cancel',
        'click a[name=save_button]': 'save',
    },

    initialize: function(options) {
        app.view.views.HeaderpaneView.prototype.initialize.call(this, options);
        var records = options.context.get("selectedDuplicates");
        this.title = app.lang.get('LBL_MERGING')+" "+records.length+" "+app.lang.get('LBL_RECORDS');
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
