({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.fallbackFieldTemplate = "edit";
        this.$el.addClass('tab-pane');
        this.$el.attr('id', 'tab-pane-' + options.submodule);
        this.meta = app.metadata.getView(options.submodule, 'edit') || {};
    }
})

