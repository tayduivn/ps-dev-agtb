/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.ListView
 * @alias SUGAR.App.layout.ListView
 * @extends View.View
 */
({
    events: {
    },


    _render: function() {
        var self = this;
        this.$el.show();
        app.view.View.prototype._render.call(this);
    },


    getData: function() {
        var name = this.model.get("name");
                if(!name)name = this.model.get('account_name');
                var city = this.model.get("billing_address_city");
                if(!city)city = this.model.get('primary_address_city');
                city = 'San Francisco';
                var url = 'Yelp/Company?limit=1&name=' + encodeURI(name);
                if(city)url += '&location=' + encodeURI(city);                var self = this;
                App.api.call('GET', app.api.buildURL(url), null, {success:function(data){
                    self.yelp = data;
                    console.log(data);
                    app.view.View.prototype._renderHtml.call(self);

                }});
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
