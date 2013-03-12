({
    plugins: ['Dashlet'],
    initialize: function(o) {
        app.view.View.prototype.initialize.call(this, o);
        if(this.model.parentModel && this.model.get("requiredModel")) {
            this.model.parentModel.on("change", this.loadData, this);
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
        var name = this.model.parentModel.get("account_name") || this.model.parentModel.get('name') || this.model.parentModel.get('full_name'),
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
        this.model.parentModel.on("change", this.loadData, this);
        app.view.View.prototype._dispose.call(this);
    }
})
