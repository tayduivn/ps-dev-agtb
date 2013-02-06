({
    events: {
        "click #addressbook_btn a[module]": "fireSearchRequest",
        "keyup #addressbook_search": "fireSearchRequest"
    },

    /**
     * Tells the address book that it should perform a search based on the module and term associated with the event.
     * Clicking on the Search button and hitting the Enter/Return key are synonymous.
     *
     * @param evt
     */
    fireSearchRequest: function(evt) {
        evt.preventDefault();

        // trigger a search if the event was something other than a keyup (most likely a click) or if the keyup event
        // was a result of pressing the Enter/Return key (13)
        if (evt.type != "keyup" || evt.which == 13) {
            var module = this.$(evt.currentTarget).attr("module"),
                term   = this.$("#addressbook_search").val();

            this.context.trigger("compose:addressbook:search", module, term);
        }
    }
})
