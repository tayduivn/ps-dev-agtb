({
    render: function() {
        var self = this;
        app.view.View.prototype.render.call(this);

        app.api.call('GET', app.api.buildURL('google/contacts'), null, {
            success: function(o) {
                self.$(".recommended-contacts").empty();
                _.each(o.contacts, function(contact) {
                    var el = $("<li />").html("<a>" +contact.first_name+" "+contact.last_name+"</a><strong>&lt;"+contact.email+"&gt;</strong>");

                    el.on("click", function() {
                        var m = app.data.createBean("Contacts");
                        m.set("first_name", contact.first_name);
                        m.set("last_name", contact.last_name);
                        m.set("email1", contact.email);
                        m.save({}, {
                            success: function(model, response) {
                                app.navigate(app.context.getContext(), model);
                            }
                        });

                    });
                    el.appendTo(".recommended-contacts");
                });
            }
        });
    }
})
