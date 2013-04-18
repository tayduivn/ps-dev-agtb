({
    /**
     * Places all components within this layout inside btn-toolbar div
     * @param component
     * @private
     */
    _placeComponent: function(component) {
        this.$el.find('.btn-toolbar').append(component.$el);
    },
    initialize: function(options) {
        if (app.config && app.config.logoURL) {
            this.logoURL=app.config.logoURL;
        }
        app.view.Layout.prototype.initialize.call(this, options);
    }

})