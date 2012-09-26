({
    initialize: function(o) {
        app.view.View.prototype.initialize.call(this, o);
        this.getData();
    },

    render: function() {
        if (!this.emails || this.emails.length <= 0) {
            this.$el.hide();
            return;
        }

        this.$el.show();
        app.view.View.prototype.render.call(this);
    },

    getData: function() {
        var email = this.model.get("email1") || this.model.get('email2');
        this.email = email;
        var self = this;
               if (email) {

                   app.api.call('GET', '../rest/v10/summer/emails?email=' + email, null, {success: function(o) {
                    self.emails = o;

                    self.render();


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