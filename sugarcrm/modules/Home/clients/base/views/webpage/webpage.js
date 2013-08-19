({
    plugins: ['Dashlet'],

    /**
     * {Integer} limit Default number of rows displayed in a dashlet.
     *
     * @protected
     */
    _defaultOptions: {
        limit: 10,
    },

    bindDataChange: function(){
        if(!this.meta.config) {
            this.model.on("change", this.render, this);
        }
    },

    _render: function() {
        if (!this.meta.config) {
            this.dashletConfig.view_panel[0].height = this.settings.get('limit') * this.rowHeight;
        }
        app.view.View.prototype._render.call(this);
    },

    initDashlet: function(view) {
        this.viewName = view;
        var settings = _.extend({}, this._defaultOptions, this.settings.attributes);
        this.settings.set(settings);
    },

    loadData: function(options) {
        if (options && options.complete) {
            options.complete();
        }
    }
})
