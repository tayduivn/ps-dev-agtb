({
    extendsFrom: 'ListView',
    toggled: false,
    bindDataChange:function () {
        app.view.invokeParent(this, {type: 'view', name: 'list', method: 'bindDataChange', platform: 'base'});
        this.on('render', this.toggleSidebar);
    },
    toggleSidebar: function () {
        if (!this.toggled) {
            app.controller.context.trigger('toggleSidebar');
            this.toggled = true;
        }
    }
})
