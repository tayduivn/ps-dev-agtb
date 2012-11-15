({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this,options);
        this.collections = {};
    },

    loadData: function() {
        var self = this,
            url = app.api.buildURL(this.module, "influencers", {"id": app.controller.context.get("model").id});

        app.api.call("read", url, null, { success: function(data) {
            _.each(data, function(key, value) {
                data[value]["picture_url"] = data[value]["picture"] ? app.api.buildFileURL({
                    module: "Users",
                    id: data[value]["id"],
                    field: "picture"
                }) : "../styleguide/assets/img/profile.png";
            });

            self.collections = data;
            self.render();
        }});
    }
})