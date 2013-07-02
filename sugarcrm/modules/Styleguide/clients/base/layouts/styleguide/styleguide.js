({
    initialize: function(options) {
        app.view.Layout.prototype.initialize.call(this, options);
    },

    _placeComponent: function(component) {
        this.$('#styleguide').append(component.$el);
    },

})
