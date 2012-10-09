({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this,options);
        this.collections = {};
    },
    loadData: function() {
        var self = this;
        app.api.call("read", app.api.buildURL(app.controller.layout.options.module + "/" + app.controller.context.get("model").id + "/" +"influencers"), null,
            { success: function(data) {
                _.each(data, function(key, value) {
                    data[value]["picture_url"] = data[value]["picture"] ? app.api.buildFileURL({
                        module: "Users",
                        id: data[value]["id"],
                        field: "picture"
                    }) : "../clients/summer/views/imagesearch/anonymous.jpg";
                });

                self.collections = data;
                self.render();
            }
        });
    }
})