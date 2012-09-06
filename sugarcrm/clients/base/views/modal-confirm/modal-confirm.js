({
    events: {
        'click [name=close_button]' : 'close',
        'click [name=ok_button]' : 'ok'
    },
    close: function(evt) {
        this.layout.parent.trigger("modal:close");
    },
    ok: function(evt) {
        this.layout.parent.trigger("modal:trigger");
    }
})