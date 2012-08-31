({
    events: {
        'click [data-dismiss="modal"]' : 'close'
    },
    close: function() {
        this.layout.hide();
    },
    _renderHtml: function() {
        var modalLayout = this.layout.getPopupComponent();
        if(modalLayout) {
            this.title = modalLayout.layout.context.get("title") || '&nbsp;';
        }
        return app.view.View.prototype._renderHtml.call(this);
    }
})