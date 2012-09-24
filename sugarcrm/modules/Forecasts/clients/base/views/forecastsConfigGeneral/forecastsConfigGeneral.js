({
    events: {
        'click [name=close_button]' : 'close',
        'click [name=ok_button]' : 'ok'
    },

    close: function(evt) {
        this.layout.context.trigger("modal:close");
    },

    ok: function(evt) {
        this.model.save();
        this.layout.context.trigger("modal:close");
    }
})