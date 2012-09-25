({
/**
 * View that displays a model pulled from the activities stream.
 * @class View.Views.PreviewView
 * @alias SUGAR.App.view.views.PreviewView
 * @extends View.View
 */
    events: {
        'click .closeSubdetail': 'closePreview'
    },
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.fallbackFieldTemplate = "detail";
    },

    _render: function() {
        this.layout.layout.layout.layout.off("dashboard:preview", null, this);
        this.layout.layout.layout.layout.on("dashboard:preview", this.togglePreview, this);

        this.$el.parent().parent().addClass("container-fluid tab-content").attr("id", "folded");
    },
    _renderHtml: function() {
        var fieldsArray, that;
        console.log(this, "_renderHtml");
        app.view.View.prototype._renderHtml.call(this);
    },

    togglePreview: function(model) {
        console.log(this, model, "test");
        var fieldsToDisplay = app.config.fieldsToDisplay || 5;
        if(model) {
            // Create a corresponding Bean and Context for clicked search result. It
            // might be a Case, a Bug, etc...we don't know, so we build dynamically.
            this.model = app.data.createBean(model.get('_module'), model.toJSON());
            this.context.set({
                'model': this.model,
                'module': this.model.module
            });

            console.log(this.context, "ctx", this.model);

            // Get the corresponding detail view meta for said module
            this.meta = app.metadata.getView(this.model.module, 'detail') || {};
            console.log(this.meta);
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

