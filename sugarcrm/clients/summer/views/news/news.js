({
    initialize: function(o) {
        app.view.View.prototype.initialize.call(this, o);
        this.getData();
    },

    render: function() {
        if (!this.responseData || this.responseData.results.length < 0) {
            this.$el.hide();
            return;
        }

        this.$el.show();
        app.view.View.prototype.render.call(this);
    },

    getData: function() {
        var name = this.model.get("name") || this.model.get('account_name') || this.model.get('full_name');

        if (name) {
            $.ajax({
                url: "https://ajax.googleapis.com/ajax/services/search/news?v=1.0&q=" + name.toLowerCase(),
                dataType: "jsonp",
                success: function(data) {
                    data.responseData.results = _.first(data.responseData.results, 3);
                    _.extend(this, data);
                    this.render();
                },
                context: this
            });
        }
    },

    bindDataChange: function() {
        var self = this;
        if (this.model) {
            this.model.on("change", function() {
                self.getData();
            }, this);
        }
    }
})