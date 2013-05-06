({
    extendsFrom: 'ListView',
    toggled: false,
    bindDataChange:function () {
        app.view.invoke(this, 'view', 'list', 'bindDataChange', {platform: 'base'});
        this.on('render', this.toggleSidebar);
    },
    toggleSidebar: function () {
        if (!this.toggled) {
            app.controller.context.trigger('toggleSidebar');
            this.toggled = true;
        }
    }
})
