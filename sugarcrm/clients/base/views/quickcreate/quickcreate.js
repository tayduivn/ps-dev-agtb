({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        this.context.on('quickcreate:clear', this.clear, this);
    },

    /**
     * Clears out field values
     */
    clear: function() {
        this.model.clear();
        this.model.set(this.model._defaults);
    }
})
