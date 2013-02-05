({
    extendsFrom: "HeaderpaneView",
    events: {
        "click [name=done_button]": "_done",
        "click [name=cancel_button]": "_cancel"
    },

    _done: function() {
        if (this.context.parent) {
            var target     = this.context.get("target"),
                recipients = this.model.get("compose_addressbook_selected_recipients");

            this.context.parent.trigger("recipients:" + target + ":add", recipients);
        }

        this._close();
    },

    _cancel: function() {
        this._close();
    },

    _close: function() {
        if (this.context.parent) {
            this.context.parent.trigger("drawer:hide");
        }

        this.context.clear();
    }
})
