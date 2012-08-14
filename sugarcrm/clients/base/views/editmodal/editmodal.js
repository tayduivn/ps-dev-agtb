({
    extends:'BaseeditmodalView',
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.fallbackFieldTemplate = "edit";
        if (this.layout) {
            this.layout.on("app:view:activity:editmodal", function() {
                this.context.set('createModel',
                    app.data.createRelatedBean(app.controller.context.get('model'), null, "notes", {})
                );
                this.render();
                this.$('.modal').modal('show');
                this.context.get('createModel').on("error:validation", function() {
                    this.resetButton();
                }, this);
            }, this);
        }
        this.bindDataChange();
    }
  })
