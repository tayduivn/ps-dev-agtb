({
    initialize: function(options) {
        var options;

        app.view.View.prototype.initialize.call(this, options);

        // Check to see if we need to make a related activity stream.
        if (this.module !== "ActivityStream") {
            if (this.context.modelId) {
                options = { module: this.module, id: this.context.modelID };
            } else {
                options = { module: this.module };
            }

            this.collection = app.data.createBeanCollection("ActivityStream");
            this.collection.fetch(options);
        }
    },

    bindDataChange: function() {
        if (this.collection) {
            this.collection.on("reset", this.render, this);
        }
    }
})