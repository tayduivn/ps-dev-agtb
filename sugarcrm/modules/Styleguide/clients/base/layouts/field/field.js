({
    plugins: ['Prettify'],

    _placeComponent: function(component) {
        this.$('#styleguide').append(component.$el);
    }

})
