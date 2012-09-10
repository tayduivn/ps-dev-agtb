({
    render: function() {
        var action = "view"
        if (this.def.link && this.def.route) {
            action = this.def.route.action;
        }
        if (!app.acl.hasAccessToModel(action, this.model)) {
            this.def.link = false;
        };
        app.view.Field.prototype.render.call(this);
    }
})