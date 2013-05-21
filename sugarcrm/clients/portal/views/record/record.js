({
    extendsFrom: 'RecordView',
    toggledClosed: false,
    initialize: function(options) {
        app.view.invokeParent(this, {type: 'view', name: 'record', method: 'initialize', platform: 'base', args:[options]});
        // Once the sidebartoggle is rendered we close the sidebar so the arrows are updated SP-719
        app.controller.context.on("sidebartoggle:rendered", this.closeSidebar, this);
    },
    closeSidebar: function () {
        if (!this.toggledClosed) {
            app.controller.context.trigger('toggleSidebar');
            this.toggledClosed = true;
        }
    }
})
