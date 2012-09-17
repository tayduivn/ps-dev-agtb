({
    initialize: function(options) {
        app.view.Layout.prototype.initialize.call(this, options);

        this.context.on('quickcreate:save', this.save, this);
    },

    save: function() {
        console.log('save');
    }
})