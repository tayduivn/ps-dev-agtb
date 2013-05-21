({
    extendsFrom: 'RecordView',
    // Starts off false so on page load (first time bindDataChange gets called) we close sidebar
    toggledClosed: false,
    initialize: function(options) {
        app.view.invokeParent(this, {type: 'view', name: 'record', method: 'initialize', platform: 'base', args:[options]});
        // Once the sidebartoggle is rendered we close the sidebar so the arrows are updated SP-719
        app.controller.context.on("sidebartoggle:rendered", this.closeSidebar, this);
    },
    closeSidebar: function () {
        // Ensure this only happens once (when the record view is initially rendered)
        if (!this.toggledClosed) {
            app.controller.context.trigger('toggleSidebar');
            this.toggledClosed = true;
        }
    }
})
