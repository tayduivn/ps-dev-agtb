({
    extendsFrom: 'RecordView',
    // Starts off false so on page load (first time bindDataChange gets called) we close sidebar
    toggledClosed: false,
    initialize: function(options) {
        app.view.invokeParent(this, {type: 'view', name: 'record', method: 'initialize', platform: 'base', args:[options]});
        var self = this;
    },
    bindDataChange:function () {
        app.view.invokeParent(this, {type: 'view', name: 'record', method: 'bindDataChange', platform: 'base'});
        this.on('render', this.closeSidebar);
    },
    closeSidebar: function () {
        if (!this.toggledClosed) {
            app.controller.context.trigger('toggleSidebar');
            this.toggledClosed = true;
        }
    }
})
