({
    events: {
        "click #addressbook_btn a[module]": "fireSearchRequest",
        "keyup #addressbook_search": "fireSearchRequest"
    },

    fireSearchRequest: function(evt) {
        evt.preventDefault();

        if (evt.type != "keyup" || evt.which == 13) {
            var module = this.$(evt.currentTarget).attr("module"),
                term   = this.$("#addressbook_search").val();

            this.context.trigger("compose:addressbook:search", module, term);
        }
    }
})
