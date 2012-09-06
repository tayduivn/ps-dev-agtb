({
    events: {
        'click [data-dismiss="modal"]' : 'close'
    },
    close: function() {
        this.layout.hide();
    },
    _renderHtml: function() {
        var self = this;
        this.title = this.context.get("title") || '&nbsp;';
        this.buttons = this.layout.context.get("buttons");
        return app.view.View.prototype._renderHtml.call(this);
    }
})