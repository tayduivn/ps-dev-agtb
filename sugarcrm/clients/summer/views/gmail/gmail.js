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
        var email = this.model.get("email1") || this.model.get('email2');

               if (email) {

                   app.api.call('GET', '../rest/v10/summer/emails?email=' + email, null, {success: function(o) {
                       console.log(o);


                   }});

    }},

    bindDataChange: function() {
        var self = this;
        if (this.model) {
            this.model.on("change", function() {
                self.getData();
            }, this);
        }
    }
})