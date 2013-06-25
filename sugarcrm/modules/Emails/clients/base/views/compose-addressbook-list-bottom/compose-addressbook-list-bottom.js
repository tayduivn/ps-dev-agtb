({
    extendsFrom: "ListBottomView",
    initialize: function(options) {
        app.view.invokeParent(this, {type: 'view', name: 'list-bottom', method: 'initialize', args:[options]});
        this.template = app.template.getView('list-bottom');
    }
})
