({
    extendsFrom: 'RecordView',
    // Starts off false so on page load (first time bindDataChange gets called) we close sidebar
    toggledClosed: false,
    initialize: function(options) {
        app.view.invokeParent(this, {type: 'view', name: 'record', method: 'initialize', platform: 'base', args:[options]});
        var self = this;
        if (this.layout) {
            this.layout.on("app:view:activity:show:preview", function(forceToggle) {
                self.openSidebar(forceToggle);
            });
        }
    },
    bindDataChange:function () {
        app.view.invokeParent(this, {type: 'view', name: 'record', method: 'bindDataChange', platform: 'base'});
        this.on('render', this.closeSidebar);
    },
    // If caller wants us to forceToggle they essentially want us to ignore our internal
    // `toggledClosed` state and just fire an unconditional toggleSidebar event
    openSidebar: function(forceToggle) {
        if (this.toggledClosed || forceToggle) {
            app.controller.context.trigger('toggleSidebar');
            this.toggledClosed = false;
        }
    },
    closeSidebar: function () {
        if (!this.toggledClosed) {
            app.controller.context.trigger('toggleSidebar');
            this.toggledClosed = true;
        }
    }
})
