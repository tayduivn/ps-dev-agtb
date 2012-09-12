({
    events: {
        'click [name=close_button]' : 'close',
        'click [name=ok_button]' : 'ok'
    },
    initialize: function(options) {
        this.message = options.message;
        app.view.View.prototype.initialize.call(this, options);
    },
    close: function(evt) {
        this.layout.context.trigger("modal:close");
    },
    ok: function(evt) {
        this.layout.context.trigger("modal:callback");
    }
})