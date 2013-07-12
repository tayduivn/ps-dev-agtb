({
    plugins: ['prettify'],

    _placeComponent: function(component) {
        this.$('#styleguide').append(component.$el);
    }

})
