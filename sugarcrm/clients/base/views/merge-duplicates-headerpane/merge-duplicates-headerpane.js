({
    extendsFrom: 'HeaderpaneView',

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
