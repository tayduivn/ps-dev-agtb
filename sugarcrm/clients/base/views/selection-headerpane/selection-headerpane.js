({
    extendsFrom: 'HeaderpaneView',

    _renderHtml: function() {
        var titleTemplate = Handlebars.compile(app.lang.getAppString("LBL_SEARCH_AND_SELECT")),
            moduleName = app.lang.get("LBL_MODULE_NAME", this.module);
        this.title = titleTemplate({module: moduleName});
        app.view.invokeParent(this, {type: 'view', name: 'headerpane', method: '_renderHtml'});
    }
})
