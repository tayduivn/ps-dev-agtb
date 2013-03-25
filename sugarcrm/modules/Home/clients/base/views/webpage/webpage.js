({
    plugins: ['Dashlet'],

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        if (this.model.parentModel && this.model.get("requiredModel")) {
            this.model.parentModel.on("change", this.loadData, this);
        } else {
            this.render();
        }
    },

    _render: function() {
        if (this.viewName !== "config") {
            this.meta.view_panel[0].height = this.model.get("height") || '400px';
        }
        app.view.View.prototype._render.call(this);
    },

    initDashlet: function(view) {
        this.viewName = view;
    },

    loadData: function(options) {
        if (options && options.complete) {
            options.complete();
        }
    },

    _dispose: function() {
        this.model.parentModel.off("change", null, this);
        app.view.View.prototype._dispose.call(this);
    }
})
