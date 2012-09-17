({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.guid = _.uniqueId("recommended_contacts");
    },

    _render: function() {
        var self = this;
        app.view.View.prototype._render.call(this);

        App.api.call('GET', '../rest/v10/summer/contacts', null, {
            success: function(o) {
                $("#"+self.guid).html("");
                _.each(o.contacts, function(contact) {
                    var el = $("<li />").html(contact.first_name+" "+contact.last_name+" <strong>&lt;"+contact.email+"&gt;</strong>");
                    el.on("click", function() {
                        var m = App.data.createBean("Contacts");
                        m.set("first_name", contact.first_name);
                        m.set("last_name", contact.last_name);
                        m.set("email1", contact.email);
                        m.save({}, {
                            success: function(model, response) {
                                App.navigate(App.context.getContext(), model);
                            }
                        });

                    });
                    el.appendTo("#"+self.guid);
                });
            }
        });
    }
})
