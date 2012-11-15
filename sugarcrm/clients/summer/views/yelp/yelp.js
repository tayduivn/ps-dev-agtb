({
    render: function() {
        this.$el.show();
        app.view.View.prototype.render.call(this);
    },

    getData: function() {
        var name = this.model.get("name");
        var self = this;
        var city;

        if (!name) {
            name = this.model.get('account_name');
        }

        city = this.model.get("billing_address_city") || this.model.get('primary_address_city') || 'San Francisco';
        var url = 'Yelp/Company?limit=1&name=' + encodeURI(name);

        if (city) {
            url += '&location=' + encodeURI(city);
        }

        app.api.call('GET', app.api.buildURL(url), null, {success: function(data) {
            self.yelp = data;
            app.view.View.prototype._renderHtml.call(self);

        }});
    },

    bindDataChange: function() {
        var self = this;
        if (this.model) {
            this.model.on("change", self.getData, this);
        }
    }
})
