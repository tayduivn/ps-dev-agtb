({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
    },

    bindDataChange: function() {
        if (this.collection) {
            this.collection.on("reset", this.render, this);
        }
    }
})