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
        // Fires on shared parent layout .. nice alternative to app.events for relatively simple page 
        this.layout.layout.off("search:preview", null, this);
        this.layout.layout.on("search:preview", this.togglePreview, this);

        this.$el.parent().parent().addClass("container-fluid tab-content").attr("id", "folded");
    },
    _renderSelf: function() {
        var fieldsArray, that;
        app.view.View.prototype._renderSelf.call(this);
    },
    
    togglePreview: function(model) {
        var fieldsToDisplay = app.config.fieldsToDisplay || 5;
        if(model) {
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
        $("li.search").removeClass("on");
    }

})

