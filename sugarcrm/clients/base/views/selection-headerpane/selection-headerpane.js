({
    extendsFrom: 'HeaderpaneView',

    _renderHtml: function() {
        var titleTemplate = Handlebars.compile(app.lang.getAppString("LBL_SEARCH_AND_SELECT"));
        this.title = titleTemplate({module: this.module});
        app.view.views.HeaderpaneView.prototype._renderHtml.call(this);
    }
})
