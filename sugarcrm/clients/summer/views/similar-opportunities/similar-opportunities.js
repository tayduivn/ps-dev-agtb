({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.collections = {};
    },

    loadData: function() {
        var self = this,
            url = app.api.buildURL(this.module, "similar", {"id": app.controller.context.get("model").id});

        app.api.call("read", url, null, { success: function(data) {
            _.each(data, function(key, value) {
                data[value]["picture_url"] = data[value]["picture"] ? app.api.buildFileURL({
                    module: "Users",
                    id: data[value]["assigned_user_id"],
                    field: "picture"
                }) : "../clients/summer/views/imagesearch/anonymous.jpg";
                data[value]['amount'] = app.currency.formatAmountLocale(data[value]['amount'], data[value]['currency_id']);
            });

            self.collections = data;
            self.render();
        }});
    },

    bindDataChange: function() {
        this.model.on("change", this.loadData, this);
    }
})
