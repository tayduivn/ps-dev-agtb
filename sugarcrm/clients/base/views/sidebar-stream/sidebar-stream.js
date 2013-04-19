({
    initialize: function(opts) {
        app.view.View.prototype.initialize.call(this, opts);

        app.events.on("preview:render", this.togglePreviewList, this);

        this.streamCollection = app.data.createBeanCollection("ActivityStream");
        this.bindDataChange();
    },

    _renderHtml: function() {
        _.each(this.streamCollection.models, function(model) {
            var picture = model.get("created_by_picture") ?
                app.api.buildFileURL({
                    module: "Users",
                    id: model.get("created_by"),
                    field: "picture"
                }) : app.config.siteUrl + "styleguide/assets/img/profile.png";

            model.set("created_by_picture_url", picture);
        });
        app.view.View.prototype._renderHtml.call(this);
    },

    togglePreviewList: function(model) {
        if (model) {
            this.model = model;
            this.streamCollection.fetch({
                //Don't show alerts for this request
                showAlerts: false,
                params: {
                    module: this.module,
                    id: this.model.id
                }
            });
        }
    },

    bindDataChange: function() {
        if (this.streamCollection) {
            this.streamCollection.on("reset", this.render, this);
        }
    },

    unbindData: function() {
        this.streamCollection.off();
        this.streamCollection = null;
        app.view.View.prototype.unbindData.call(this);
    }
})