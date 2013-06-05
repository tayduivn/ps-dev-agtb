({
    plugins: ['Dashlet'],

    bindDataChange: function(){
        if(!this.meta.config) {
            this.model.on("change", this.render, this);
        }
    },

    _render: function() {
        if (!this.meta.config) {
            this.dashletConfig.view_panel[0].height = this.settings.get("height") || '400px';
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
    }
})
