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
            this.model.set(model);
            this.model.module = this.model.get('_module');
            this.context.set({
                'model': this.model,
                'module': this.model.module
            });
            // Get the corresponding view meta
            this.meta = app.metadata.getView(this.model.module, 'detail') || {};

            // Clip meta panel fields to first <N> fields
            this.meta.panels[0].fields = _.first(this.meta.panels[0].fields, fieldsToDisplay);

            // in turn calls _renderSelf, but also populates our fields via _renderField
            app.view.View.prototype._render.call(this);
        }
    },
    closePreview: function() {
        this.model.clear();
        this.$el.empty();
        $("li.search").removeClass("on");
    }

})

