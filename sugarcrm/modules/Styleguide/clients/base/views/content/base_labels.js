function _init_content(view, app) {
    view.pageData.module_list = _.without(app.metadata.getModuleNames({filter: 'display_tab', access: 'read'}), 'Home');
    view.pageData.module_list.sort();
}
