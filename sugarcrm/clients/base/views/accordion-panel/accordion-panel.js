({
    initialize: function(options) {
        debugger;
        // Get the corresponding detail view meta for said module


        app.view.View.prototype.initialize.call(this, options);
        this.fallbackFieldTemplate = "edit";


    },

    _renderHtml: function() {
        this.meta = app.metadata.getView(this.model.module, 'edit') || {};

        // Clip meta panel fields to first N number of fields per the spec
        this.meta.panels[0].fields = _.first(this.meta.panels[0].fields, 5);

        app.view.View.prototype._renderHtml.call(this);

    }

})

