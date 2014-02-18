({
    className: 'container-fluid',
    module_list: [],

    initialize: function(options) {
        this.module_list = _.without(app.metadata.getModuleNames({filter: 'display_tab', access: 'read'}), 'Home');
        this.module_list.sort();
    }
})
