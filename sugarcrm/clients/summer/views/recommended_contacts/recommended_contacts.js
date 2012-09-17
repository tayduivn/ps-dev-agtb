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
                    el.appendTo("#"+self.guid);
                });
            }
        });
    }
})
