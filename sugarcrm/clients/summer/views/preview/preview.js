({
    events: {
        'click .closeSubdetail': 'closePreview'
    },

    initialize: function(options) {
        _.bindAll(this);

        app.view.View.prototype.initialize.call(this, options);
        this.fallbackFieldTemplate = "detail";
        this.context.on("togglePreview", this.togglePreview);
    },

    _render: function() {
        this.$el.parent().parent().addClass("container-fluid tab-content");
    },

    _renderHtml: function() {
        var fieldsArray;
        app.view.View.prototype._renderHtml.call(this);
    },

    togglePreview: function(model) {
        var fieldsToDisplay = app.config.fieldsToDisplay || 5;
        if (model) {
            // Create a corresponding Bean and Context for clicked search result. It
            // might be a Case, a Bug, etc...we don't know, so we build dynamically.
            this.model = app.data.createBean(model.get('_module'), model.toJSON());
            this.context.set({
                'model': this.model,
                'module': this.model.module
            });

            // Get the corresponding detail view meta for said module
            this.meta = app.metadata.getView(this.model.module, 'detail') || {};
            // Clip meta panel fields to first N number of fields per the spec
            this.meta.panels[0].fields = _.first(this.meta.panels[0].fields, fieldsToDisplay);

            app.view.View.prototype._render.call(this);
        }
    },

    closePreview: function() {
        this.model.clear();
        this.$el.empty();
    }

})

