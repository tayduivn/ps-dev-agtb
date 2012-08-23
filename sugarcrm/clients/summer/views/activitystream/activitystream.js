({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        console.log("This", this);

        // Check to see if we need to make a related activity stream.
        if (this.module !== "ActivityStream") {
            console.log("Not an activity stream");
            this.collection = app.data.createBeanCollection("ActivityStream");
            this.collection.fetch({
                params: {
                    module: this.module,
                    id: this.model.id
                }
            });
        }
    },

    bindDataChange: function() {
        if (this.collection) {
            this.collection.on("reset", this.render, this);
        }
    }
})