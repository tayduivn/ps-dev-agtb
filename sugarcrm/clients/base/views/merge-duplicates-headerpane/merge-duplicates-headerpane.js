({
    extendsFrom: 'HeaderpaneView',

    events: {
        'click a[name=cancel_button]': 'cancel',
    },

    initialize: function(options) {
        app.view.views.HeaderpaneView.prototype.initialize.call(this, options);
    },

    /**
     * Set the title
     */
    _renderHtml: function() {
        this.title = app.lang.get("LBL_MERGE_DUPLICATES");;
        app.view.views.HeaderpaneView.prototype._renderHtml.call(this);
    },

    /**
     * Cancel and close the drawer
     */
    cancel: function() {
        this.context.trigger("drawer:hide");
        if (this.context.parent)
            this.context.parent.trigger("drawer:hide");
    }
})
