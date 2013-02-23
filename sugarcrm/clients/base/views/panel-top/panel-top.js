({
    className: "subpanel-header",
    events: {
        "click .btn-invisible": "hidePanel",
        "click a[name=create_button]": "openCreateDrawer"
    },

    initialize: function(opts) {
        app.view.View.prototype.initialize.call(this, opts);
    },

    hidePanel: function(e) {
        var target = this.$(e.currentTarget),
            data = target.data();

        this.layout.trigger("hide", data.visible);
        target.data("visible", !data.visible);
    },

    openCreateDrawer: function() {
        var model = app.data.createRelatedBean(this.model, null, this.context.get('link'));
        app.drawer.open({
            layout: 'create',
            context: {
                create: true,
                model: model
            }
        });
    },

    bindDataChange: function() {
        if (this.collection) {
            this.collection.on('reset', this.render, this);
        }
    }
})
