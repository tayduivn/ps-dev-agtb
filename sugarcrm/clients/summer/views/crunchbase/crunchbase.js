({
    render: function() {
        if (this.name != 'crunchbase') {
            app.view.View.prototype.render.call(this);
        }
    },

    getData: function() {
        var url,
            name = this.model.get("name") || this.model.get('account_name') || this.model.get('full_name');

        if (name) {
            url = "http://api.crunchbase.com/v/1/company/" + name.toLowerCase().replace(/ /g, "-") + ".js?callback=?";
            $.ajax({
                url: url,
                dataType: "jsonp",
                success: function(data) {
                    if (data.image) {
                        data['image'] = data.image.available_sizes[0][1];
                    }

                    _.extend(this, data);
                    this.render();
                },
                context: this
            });
        }
    },

    bindDataChange: function() {
        if (this.model) {
            this.model.on("change", this.getData, this);
        }
    }
})
