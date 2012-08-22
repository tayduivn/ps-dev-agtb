({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        console.log("This", this);

        // Check to see if we need to make a related activity stream.
        if (this.module !== "ActivityStream") {
            this.collection = app.data.createBeanCollection("ActivityStream", []);
        }
    },

    bindDataChange: function() {
        if (this.collection) {
            this.collection.on("reset", this.render, this);
        }
    }
})