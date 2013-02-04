({
    renderHtml: function() {
        app.view.Layout.prototype.renderHtml.call(this);
        this.$el.addClass("preview-pane");
    }
})