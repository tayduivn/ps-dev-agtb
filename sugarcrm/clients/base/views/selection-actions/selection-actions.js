({
    _renderHtml: function() {
        var titleTemplate = Handlebars.compile(app.lang.getAppString("LBL_SEARCH_AND_SELECT"));
        this.title = titleTemplate({module: this.module});
        app.view.View.prototype._renderHtml.call(this);
    }
})
