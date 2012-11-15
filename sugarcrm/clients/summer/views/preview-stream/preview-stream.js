({
    initialize: function(opts) {
        _.bindAll(this);

        app.view.View.prototype.initialize.call(this, opts);

        this.context.on("togglePreview", this.togglePreviewList);
        this.collection = null;
    },

    togglePreviewList: function(model) {
        if (model) {
            this.model = model;
            this.collection = app.data.createBeanCollection("ActivityStream");
            this.bindDataChange();
            this.collection.fetch({ params: { module: this.module, id: this.model.id }});
        }
    },

    bindDataChange: function() {
        if (this.collection) {
            this.collection.on("reset", this.render, this);
        }
    }
})