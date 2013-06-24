({
    extendsFrom: 'CreateView',

    initialize: function(options) {
        app.view.invokeParent(this, {type: 'view', name: 'create', method: 'initialize', platform: 'base', args:[options]});
        this.template = app.template.getView('record');
        this.enableDuplicateCheck = false;
    }
})
