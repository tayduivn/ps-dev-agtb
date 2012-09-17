({
    initialize: function(options) {
        app.view.Layout.prototype.initialize.call(this, options);

        this.context.on('quickcreate:save', this.save, this);
    },

    save: function() {
        this.model.save();
        this.context.parent.trigger('modal:close');
    }
})