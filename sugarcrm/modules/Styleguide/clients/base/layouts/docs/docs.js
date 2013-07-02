({
    loadPrettyPrint: true,

    initialize: function(options) {
        app.view.Layout.prototype.initialize.call(this, options);
        if (window.prettyPrint) {
            this.loadPrettyPrint = false;
        }
    },

    _placeComponent: function(component) {
        this.$('#styleguide').append(component.$el);
    },

})
