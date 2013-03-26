({
    plugins: ['Dashlet'],
    initialize: function(o) {
        app.view.View.prototype.initialize.call(this, o);
        if(this.context.parent.parent && this.context.parent.parent.get("model")) {
            this.targetModel = this.context.parent.parent.get("model");
            this.targetModel.on("change", this.loadData, this);
        }
    },

    _render: function() {
        if (_.isEmpty(this.responseData)) {
            this.hide();
            return;
        }

        this.show();

        app.view.View.prototype._render.call(this);
    },

    loadData: function(options) {
        if(_.isUndefined(this.targetModel)){
            return;
        }
        var name = this.targetModel.get("account_name") || this.targetModel.get('name') || this.targetModel.get('full_name'),
            limit = parseInt(this.model.get("limit") || 20, 10);

        if (name) {
            $.ajax({
                url: "https://ajax.googleapis.com/ajax/services/search/news?v=1.0&q=" + name.toLowerCase(),
                dataType: "jsonp",
                success: function(data) {
                    data.responseData.results = _.first(data.responseData.results, limit);
                    _.extend(this, data);
                    this.render();
                },
                context: this,
                complete: (options) ? options.complete : null
            });
        }
    },

    _dispose: function() {
        if (this.targetModel)
            this.targetModel.off("change", this.loadData, this);
        app.view.View.prototype._dispose.call(this);
    }
})
