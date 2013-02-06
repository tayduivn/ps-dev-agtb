({
    extendsFrom: "HeaderpaneView",
    events: {
        "click [name=done_button]": "_done",
        "click [name=cancel_button]": "_cancel"
    },

    /**
     * The user clicked the Done button so trigger an event to add selected recipients from the address book to the
     * target field and then close the drawer.
     *
     * @private
     */
    _done: function() {
        // don't bother triggering an event if this.context.parent doesn't exist
        if (this.context.parent) {
            var target     = this.context.get("target"),
                recipients = this.model.get("compose_addressbook_selected_recipients");

            this.context.parent.trigger("recipients:" + target + ":add", recipients);
        }

        this._cancel();
    },

    /**
     * Close the drawer.
     *
     * @private
     */
    _cancel: function() {
        // don't bother triggering an event if this.context.parent doesn't exist
        if (this.context.parent) {
            this.context.parent.trigger("drawer:hide");
        }

        this.context.clear();
    }
})
