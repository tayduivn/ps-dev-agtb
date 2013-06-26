({
    extendsFrom: 'HeaderpaneView',

    initialize: function(options) {
        app.view.invokeParent(this, {type: 'view', name: 'headerpane', method: 'initialize', args:[options]});
    },

    events: {
        'click a[name=cancel_button]': 'cancel'
    },

    /**
     * Cancel and close the drawer
     */
    cancel: function() {
        app.drawer.close();
    }
})
