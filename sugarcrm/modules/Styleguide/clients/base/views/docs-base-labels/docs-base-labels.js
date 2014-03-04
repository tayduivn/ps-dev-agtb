({
    className: 'container-fluid',
    module_list: [],

    _renderHtml: function () {
        this.module_list = _.without(app.metadata.getModuleNames({filter: 'display_tab', access: 'read'}), 'Home');
        this.module_list.sort();
        this._super('_renderHtml');
    }
})
